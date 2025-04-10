<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\Admin\DailySummary;
use Illuminate\Support\Facades\DB;
use App\Models\SeamlessTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;

class DailySummaryController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';
    protected const MAX_REPORTS_PER_DAY = 10000; // Maximum allowed reports per day

    public function index(Request $request)
{
    // Log the incoming request parameters
    // Log::debug('Index method called', [
    //     'request_params' => $request->all(),
    //     'user_id' => Auth::id(),
    // ]);

    $agent = $this->getAgent() ?? Auth::user()->load('roles');

    // Log the agent details
    // Log::debug('Agent determined', [
    //     'agent_id' => $agent->id,
    //     'agent_roles' => $agent->roles->pluck('name')->toArray(),
    // ]);

    $hierarchy = [
        'Owner' => ['Senior', 'Master', 'Agent'],
        'Senior' => ['Master', 'Agent'],
        'Master' => ['Agent'],
    ];

    $query = DailySummary::query()
            ->join('users', 'users.user_name', '=', 'daily_summaries.member_name')
            ->select('daily_summaries.*');
        
    // Log the initial query state
    // Log::debug('Initial query built', [
    //     'sql' => $query->toSql(),
    //     'bindings' => $query->getBindings(),
    // ]);

    // Set default date range if not provided (e.g., last 7 days)
    $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(7);
    $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

    $query->whereDate('report_date', '>=', $startDate);
    // Log::debug('Applied start_date filter', [
    //     'start_date' => $startDate->toDateString(),
    //     'sql' => $query->toSql(),
    //     'bindings' => $query->getBindings(),
    // ]);

    $query->whereDate('report_date', '<=', $endDate);
    // Log::debug('Applied end_date filter', [
    //     'end_date' => $endDate->toDateString(),
    //     'sql' => $query->toSql(),
    //     'bindings' => $query->getBindings(),
    // ]);

    if ($agent->hasRole('Senior Owner')) {
        $result = $query;
        Log::debug('Agent has Senior Owner role, no additional filtering applied');
    } elseif ($agent->hasRole('Agent')) {
        $agentChildrenIds = $agent->children->pluck('id')->toArray();
        $result = $query->whereIn('users.id', $agentChildrenIds);
        // Log::debug('Agent role detected, filtered by children IDs', [
        //     'agent_children_ids' => $agentChildrenIds,
        //     'sql' => $result->toSql(),
        //     'bindings' => $result->getBindings(),
        // ]);
    } else {
        $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
        $result = $query->whereIn('users.id', $agentChildrenIds);
        // Log::debug('Non-Senior Owner/Agent role, filtered by hierarchical children IDs', [
        //     'agent_children_ids' => $agentChildrenIds,
        //     'sql' => $result->toSql(),
        //     'bindings' => $result->getBindings(),
        // ]);
    }

    // Log before executing the query
    // Log::debug('Executing final query for summaries', [
    //     'sql' => $result->toSql(),
    //     'bindings' => $result->getBindings(),
    // ]);

    $summaries = $result->orderBy('report_date', 'desc')
        ->paginate(10)
        ->appends($request->only(['start_date', 'end_date'])); // Preserve query parameters in pagination links

    // Log the pagination results
    // Log::debug('Summaries retrieved', [
    //     'total' => $summaries->total(),
    //     'per_page' => $summaries->perPage(),
    //     'current_page' => $summaries->currentPage(),
    //     'summary_ids' => $summaries->pluck('id')->toArray(),
    // ]);

    // // Log the pagination links to verify query parameters
    // Log::debug('Pagination links generated', [
    //     'links' => $summaries->toArray()['links'],
    //     'appended_params' => $request->only(['start_date', 'end_date']),
    // ]);

    return view('admin.daily_summaries.index', compact('summaries'));
}
    // public function index(Request $request)
    // {
    //     $agent = $this->getAgent() ?? Auth::user();

    //     $hierarchy = [
    //         'Owner' => ['Senior', 'Master', 'Agent'],
    //         'Senior' => ['Master', 'Agent'],
    //         'Master' => ['Agent'],
    //     ];

    //     $query = DailySummary::query()
    //             ->join('users', 'users.user_name', '=', 'daily_summaries.member_name')
    //             ->select('daily_summaries.*');
            
    //     // Apply date filters if provided
    //     if ($request->filled('start_date')) {
    //         $query->whereDate('report_date', '>=', Carbon::parse($request->start_date));
    //     }

    //     if ($request->filled('end_date')) {
    //         $query->whereDate('report_date', '<=', Carbon::parse($request->end_date));
    //     }
    //     if ($agent->hasRole('Senior Owner')) {
    //         $result = $query;
    //     } elseif ($agent->hasRole('Agent')) {
    //         $agentChildrenIds = $agent->children->pluck('id')->toArray();
    //         $result = $query->whereIn('users.id', $agentChildrenIds);
    //     } else {
    //         $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
    //         $result = $query->whereIn('users.id', $agentChildrenIds);
    //     }

    //     $summaries = $result->orderBy('report_date', 'desc')
    //         ->paginate(10)
    //         ->appends($request->only(['start_date', 'end_date'])); // Preserve query parameters in pagination links

    //     return view('admin.daily_summaries.index', compact('summaries'));
    // }

    public function generateSummaries(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        // Limit date range to prevent memory issues
        if ($startDate->diffInDays($endDate) > 31) {
            return response()->json([
                'success' => false,
                'message' => 'Date range cannot exceed 31 days'
            ], 400);
        }

        try {
            // Check the total number of reports in the date range
            $totalReports = DB::table('reports')
                ->whereBetween('created_on', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count();

            $daysInRange = $startDate->diffInDays($endDate) + 1;
            $averageReportsPerDay = $totalReports / $daysInRange;

            if ($averageReportsPerDay > self::MAX_REPORTS_PER_DAY) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many reports to process. Average reports per day ($averageReportsPerDay) exceeds the limit of " . self::MAX_REPORTS_PER_DAY,
                    'total_reports' => $totalReports,
                    'days' => $daysInRange,
                ], 400);
            }

            $period = CarbonPeriod::create($startDate, $endDate);
            $processedDates = [];
            $totalSummaries = 0;
            $totalReportsDeleted = 0;

            // Wrap the entire process in a transaction
            DB::transaction(function () use ($period, &$processedDates, &$totalSummaries, &$totalReportsDeleted) {
                foreach ($period as $date) {
                    $startOfDay = $date->copy()->startOfDay();
                    $endOfDay = $date->copy()->endOfDay();

                    // Generate all summary types for the current date
                    $this->generateOverallSummary($date, $startOfDay, $endOfDay);
                    $this->generateAgentSummaries($date, $startOfDay, $endOfDay);
                    $this->generateMemberSummaries($date, $startOfDay, $endOfDay);

                    // After successfully generating summaries, delete the corresponding reports
                    $deletedCount = DB::table('reports')
                        ->whereBetween('created_on', [$startOfDay, $endOfDay])
                        ->delete();

                    Log::info('Deleted reports for date', [
                        'date' => $date->format('Y-m-d'),
                        'deleted_count' => $deletedCount,
                    ]);

                    $totalReportsDeleted += $deletedCount;
                    $processedDates[] = $date->format('Y-m-d');
                    $totalSummaries += $this->getSummaryCountForDate($date);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Daily summaries generated successfully and reports deleted!",
                'processed_dates' => $processedDates,
                'total_summaries_created' => $totalSummaries,
                'total_reports_deleted' => $totalReportsDeleted,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summaries or delete reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function getSummaryCountForDate($date)
    {
        return DB::table('daily_summaries')
            ->whereDate('report_date', $date->format('Y-m-d'))
            ->count();
    }

    protected function generateOverallSummary($date, $startOfDay, $endOfDay)
    {
        $summary = DB::table('reports')
            ->select(
                DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
                DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
                DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
                DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->first();

        DB::table('daily_summaries')->updateOrInsert(
            [
                'report_date' => $date->format('Y-m-d'),
                'member_name' => null,
                'agent_id' => null
            ],
            [
                'total_valid_bet_amount' => $summary->total_valid_bet_amount,
                'total_payout_amount' => $summary->total_payout_amount,
                'total_bet_amount' => $summary->total_bet_amount,
                'total_win_amount' => $summary->total_win_amount,
                'total_lose_amount' => $summary->total_lose_amount,
                'total_stake_count' => $summary->total_stake_count,
                'updated_at' => now(),
            ]
        );
    }

    protected function generateAgentSummaries($date, $startOfDay, $endOfDay)
    {
        // Use a cursor to process records one at a time
        $agentSummaries = DB::table('reports')
            ->select(
                'agent_id',
                DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
                DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
                DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
                DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->groupBy('agent_id')
            ->cursor();

        foreach ($agentSummaries as $summary) {
            DB::table('daily_summaries')->updateOrInsert(
                [
                    'report_date' => $date->format('Y-m-d'),
                    'member_name' => null,
                    'agent_id' => $summary->agent_id
                ],
                [
                    'total_valid_bet_amount' => $summary->total_valid_bet_amount,
                    'total_payout_amount' => $summary->total_payout_amount,
                    'total_bet_amount' => $summary->total_bet_amount,
                    'total_win_amount' => $summary->total_win_amount,
                    'total_lose_amount' => $summary->total_lose_amount,
                    'total_stake_count' => $summary->total_stake_count,
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function generateMemberSummaries($date, $startOfDay, $endOfDay)
    {
        // Use a cursor to process records one at a time
        $memberSummaries = DB::table('reports')
            ->select(
                'member_name',
                'agent_id',
                DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
                DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
                DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
                DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
            )
            ->whereBetween('created_on', [$startOfDay, $endOfDay])
            ->groupBy('member_name', 'agent_id')
            ->cursor();

        foreach ($memberSummaries as $summary) {
            DB::table('daily_summaries')->updateOrInsert(
                [
                    'report_date' => $date->format('Y-m-d'),
                    'member_name' => $summary->member_name,
                    'agent_id' => $summary->agent_id
                ],
                [
                    'total_valid_bet_amount' => $summary->total_valid_bet_amount,
                    'total_payout_amount' => $summary->total_payout_amount,
                    'total_bet_amount' => $summary->total_bet_amount,
                    'total_win_amount' => $summary->total_win_amount,
                    'total_lose_amount' => $summary->total_lose_amount,
                    'total_stake_count' => $summary->total_stake_count,
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function isExistingAgent($userId)
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
    }

    private function getAgent()
    {
        return $this->isExistingAgent(Auth::id());
    }

    private function getAgentChildrenIds($agent, array $hierarchy)
    {
        foreach ($hierarchy as $role => $levels) {
            if ($agent->hasRole($role)) {
                return collect([$agent])
                    ->flatMap(fn($levelAgent) => $this->getChildrenRecursive($levelAgent, $levels))
                    ->pluck('id')
                    ->toArray();
            }
        }

        return [];
    }

    private function getChildrenRecursive($agent, array $levels)
    {
        $children = collect([$agent]);
        foreach ($levels as $level) {
            $children = $children->flatMap->children;
        }

        return $children->flatMap->children;
    }

    public function SeamlessTransactionIndex(Request $request)
    {
        // Log the incoming request parameters
        // Log::debug('SeamlessTransaction index method called', [
        //     'request_params' => $request->all(),
        // ]);

        // Fetch seamless transactions, ordered by created_at descending, paginated 10 per page
        $transactions = SeamlessTransaction::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Log the retrieved transactions
        // Log::debug('Seamless transactions retrieved', [
        //     'total' => $transactions->total(),
        //     'per_page' => $transactions->perPage(),
        //     'current_page' => $transactions->currentPage(),
        //     'transaction_ids' => $transactions->pluck('id')->toArray(),
        // ]);

        return view('admin.daily_summaries.seamless_transaction_index', compact('transactions'));
    }

    public function deleteByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        try {
            // Log the deletion request
            // Log::info('Deleting seamless transactions by date range', [
            //     'start_date' => $startDate->toDateTimeString(),
            //     'end_date' => $endDate->toDateTimeString(),
            // ]);

            // Delete records within the date range
            $deletedCount = DB::transaction(function () use ($startDate, $endDate) {
                return SeamlessTransaction::whereBetween('created_at', [$startDate, $endDate])
                    ->delete();
            });

            // Log the result of the deletion
            // Log::info('Seamless transactions deleted', [
            //     'deleted_count' => $deletedCount,
            // ]);

            return redirect()->route('admin.seamless_transactions.index')
                ->with('success', "Successfully deleted $deletedCount transactions between $startDate and $endDate.");
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to delete seamless transactions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.seamless_transactions.index')
                ->with('error', 'Failed to delete transactions: ' . $e->getMessage());
        }
    }

    public function TransactionCleanupIndex(Request $request)
    {
        // Fetch users for selection (e.g., paginated list)
        $users = User::query()
            ->orderBy('user_name')
            ->paginate(10);

        return view('admin.daily_summaries.transaction_cleanup_index', compact('users'));
    }

    public function delete(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->input('user_id');

        try {
            // Log the deletion request
            Log::info('Starting transaction cleanup for user', [
                'user_id' => $userId,
            ]);

            // Find the user
            $user = User::findOrFail($userId);

            // Start a transaction to ensure data integrity
            $deletedCount = DB::transaction(function () use ($user) {
                // Find wallets belonging to the user
                $walletIds = Wallet::where('holder_type', 'App\Models\User')
                    ->where('holder_id', $user->id)
                    ->pluck('id');

                if ($walletIds->isEmpty()) {
                    Log::info('No wallets found for user', [
                        'user_id' => $user->id,
                    ]);
                    return 0;
                }

                // Log the wallet balances before deletion (for auditing)
                $wallets = Wallet::whereIn('id', $walletIds)->get();
                foreach ($wallets as $wallet) {
                    Log::info('Wallet balance before transaction deletion', [
                        'wallet_id' => $wallet->id,
                        'user_id' => $user->id,
                        'balance' => $wallet->balance,
                    ]);
                }

                // Delete transactions associated with the user's wallets in chunks
                $totalDeleted = 0;
                Transaction::whereIn('wallet_id', $walletIds)
                    ->chunk(1000, function ($transactions) use (&$totalDeleted) {
                        $count = $transactions->count();
                        Transaction::whereIn('id', $transactions->pluck('id'))->delete();
                        $totalDeleted += $count;
                        Log::info('Deleted batch of transactions', [
                            'count' => $count,
                            'total_deleted' => $totalDeleted,
                        ]);
                    });

                // Delete transactions where the user is the payable directly
                $payableDeleted = Transaction::where('payable_type', 'App\Models\User')
                    ->where('payable_id', $user->id)
                    ->delete();

                $totalDeleted += $payableDeleted;

                Log::info('Deleted payable transactions for user', [
                    'user_id' => $user->id,
                    'payable_deleted' => $payableDeleted,
                ]);

                // Log the wallet balances after deletion (for verification)
                foreach ($wallets as $wallet) {
                    $wallet->refresh(); // Refresh the wallet to get the latest balance
                    Log::info('Wallet balance after transaction deletion', [
                        'wallet_id' => $wallet->id,
                        'user_id' => $user->id,
                        'balance' => $wallet->balance,
                    ]);
                }

                return $totalDeleted;
            });

            // Log the result
            Log::info('Transaction cleanup completed', [
                'user_id' => $user->id,
                'total_deleted' => $deletedCount,
            ]);

            return redirect()->route('admin.transaction_cleanup.index')
                ->with('success', "Successfully deleted $deletedCount transactions for user {$user->user_name}. Wallet balances remain unchanged.");
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to delete transactions for user', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.transaction_cleanup.index')
                ->with('error', 'Failed to delete transactions: ' . $e->getMessage());
        }
    }

    // public function delete(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //     ]);

    //     $userId = $request->input('user_id');

    //     try {
    //         // Log the deletion request
    //         Log::info('Starting transaction cleanup for user', [
    //             'user_id' => $userId,
    //         ]);

    //         // Find the user
    //         $user = User::findOrFail($userId);

    //         // Start a transaction to ensure data integrity
    //         $deletedCount = DB::transaction(function () use ($user) {
    //             // Find wallets belonging to the user
    //             $walletIds = Wallet::where('holder_type', 'App\Models\User')
    //                 ->where('holder_id', $user->id)
    //                 ->pluck('id');

    //             if ($walletIds->isEmpty()) {
    //                 Log::info('No wallets found for user', [
    //                     'user_id' => $user->id,
    //                 ]);
    //                 return 0;
    //             }

    //             // Delete transactions associated with the user's wallets in chunks
    //             $totalDeleted = 0;
    //             Transaction::whereIn('wallet_id', $walletIds)
    //                 ->chunk(1000, function ($transactions) use (&$totalDeleted) {
    //                     $count = $transactions->count();
    //                     Transaction::whereIn('id', $transactions->pluck('id'))->delete();
    //                     $totalDeleted += $count;
    //                     Log::info('Deleted batch of transactions', [
    //                         'count' => $count,
    //                         'total_deleted' => $totalDeleted,
    //                     ]);
    //                 });

    //             // Optionally, delete the wallets if you want to clean them up as well
    //             // Wallet::whereIn('id', $walletIds)->delete(); // This will cascade delete transactions due to foreign key

    //             // Delete transactions where the user is the payable directly
    //             $payableDeleted = Transaction::where('payable_type', 'App\Models\User')
    //                 ->where('payable_id', $user->id)
    //                 ->delete();

    //             $totalDeleted += $payableDeleted;

    //             Log::info('Deleted payable transactions for user', [
    //                 'user_id' => $user->id,
    //                 'payable_deleted' => $payableDeleted,
    //             ]);

    //             return $totalDeleted;
    //         });

    //         // Log the result
    //         Log::info('Transaction cleanup completed', [
    //             'user_id' => $user->id,
    //             'total_deleted' => $deletedCount,
    //         ]);

    //         return redirect()->route('admin.transaction_cleanup.index')
    //             ->with('success', "Successfully deleted $deletedCount transactions for user {$user->user_name}.");
    //     } catch (\Exception $e) {
    //         // Log the error
    //         Log::error('Failed to delete transactions for user', [
    //             'user_id' => $userId,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->route('admin.transaction_cleanup.index')
    //             ->with('error', 'Failed to delete transactions: ' . $e->getMessage());
    //     }
    // }
}

// class DailySummaryController extends Controller
// {
//     protected const SUB_AGENT_ROlE = 'Sub Agent';

//     public function index(Request $request)
//     {
//         $agent = $this->getAgent() ?? Auth::user();

//         $hierarchy = [
//             'Owner' => ['Senior', 'Master', 'Agent'],
//             'Senior' => ['Master', 'Agent'],
//             'Master' => ['Agent'],
//         ];

//         $query = DailySummary::query()
//                 ->join('users', 'users.user_name', '=', 'daily_summaries.member_name')
//                 ->select('daily_summaries.*');
            
//         // Apply date filters if provided
//         if ($request->filled('start_date')) {
//             $query->whereDate('report_date', '>=', Carbon::parse($request->start_date));
//         }

//         if ($request->filled('end_date')) {
//             $query->whereDate('report_date', '<=', Carbon::parse($request->end_date));
//         }
//         if ($agent->hasRole('Senior Owner')) {
//             $result = $query;
//         } elseif ($agent->hasRole('Agent')) {
//             $agentChildrenIds = $agent->children->pluck('id')->toArray();
//             $result = $query->whereIn('users.id', $agentChildrenIds);
//         } else {
//             $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
//             $result = $query->whereIn('users.id', $agentChildrenIds);
//         }

//         $summaries = $result->orderBy('report_date', 'desc')
//             ->paginate(10)
//             ->appends($request->only(['start_date', 'end_date'])); // Preserve query parameters in pagination links

//         return view('admin.daily_summaries.index', compact('summaries'));
//     }

//     public function generateSummaries(Request $request)
//     {
//         $request->validate([
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date'
//         ]);

//         $startDate = Carbon::parse($request->input('start_date'));
//         $endDate = Carbon::parse($request->input('end_date'));

//         // Limit date range to prevent memory issues
//         if ($startDate->diffInDays($endDate) > 31) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Date range cannot exceed 31 days'
//             ], 400);
//         }

//         try {
//             $period = CarbonPeriod::create($startDate, $endDate);
//             $processedDates = [];
//             $totalSummaries = 0;
//             $totalReportsDeleted = 0;

//             // Wrap the entire process in a transaction
//             DB::transaction(function () use ($period, &$processedDates, &$totalSummaries, &$totalReportsDeleted) {
//                 foreach ($period as $date) {
//                     $startOfDay = $date->copy()->startOfDay();
//                     $endOfDay = $date->copy()->endOfDay();

//                     // Generate all summary types for the current date
//                     $this->generateOverallSummary($date, $startOfDay, $endOfDay);
//                     $this->generateAgentSummaries($date, $startOfDay, $endOfDay);
//                     $this->generateMemberSummaries($date, $startOfDay, $endOfDay);

//                     // After successfully generating summaries, delete the corresponding reports
//                     $deletedCount = DB::table('reports')
//                         ->whereBetween('created_on', [$startOfDay, $endOfDay])
//                         ->delete();

//                     Log::info('Deleted reports for date', [
//                         'date' => $date->format('Y-m-d'),
//                         'deleted_count' => $deletedCount,
//                     ]);

//                     $totalReportsDeleted += $deletedCount;
//                     $processedDates[] = $date->format('Y-m-d');
//                     $totalSummaries += $this->getSummaryCountForDate($date);
//                 }
//             });

//             return response()->json([
//                 'success' => true,
//                 'message' => "Daily summaries generated successfully and reports deleted!",
//                 'processed_dates' => $processedDates,
//                 'total_summaries_created' => $totalSummaries,
//                 'total_reports_deleted' => $totalReportsDeleted,
//                 'date_range' => [
//                     'start' => $startDate->format('Y-m-d'),
//                     'end' => $endDate->format('Y-m-d')
//                 ]
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to generate summaries or delete reports',
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }

//     protected function getSummaryCountForDate($date)
//     {
//         return DB::table('daily_summaries')
//             ->whereDate('report_date', $date->format('Y-m-d'))
//             ->count();
//     }

//     protected function generateOverallSummary($date, $startOfDay, $endOfDay)
//     {
//         $summary = DB::table('reports')
//             ->select(
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->first();

//         DB::table('daily_summaries')->updateOrInsert(
//             [
//                 'report_date' => $date->format('Y-m-d'),
//                 'member_name' => null,
//                 'agent_id' => null
//             ],
//             [
//                 'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                 'total_payout_amount' => $summary->total_payout_amount,
//                 'total_bet_amount' => $summary->total_bet_amount,
//                 'total_win_amount' => $summary->total_win_amount,
//                 'total_lose_amount' => $summary->total_lose_amount,
//                 'total_stake_count' => $summary->total_stake_count,
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ]
//         );
//     }

//     protected function generateAgentSummaries($date, $startOfDay, $endOfDay)
//     {
//         $agentSummaries = DB::table('reports')
//             ->select(
//                 'agent_id',
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->groupBy('agent_id')
//             ->get();

//         foreach ($agentSummaries as $summary) {
//             DB::table('daily_summaries')->updateOrInsert(
//                 [
//                     'report_date' => $date->format('Y-m-d'),
//                     'member_name' => null,
//                     'agent_id' => $summary->agent_id
//                 ],
//                 [
//                     'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                     'total_payout_amount' => $summary->total_payout_amount,
//                     'total_bet_amount' => $summary->total_bet_amount,
//                     'total_win_amount' => $summary->total_win_amount,
//                     'total_lose_amount' => $summary->total_lose_amount,
//                     'total_stake_count' => $summary->total_stake_count,
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ]
//             );
//         }
//     }

//     protected function generateMemberSummaries($date, $startOfDay, $endOfDay)
//     {
//         $memberSummaries = DB::table('reports')
//             ->select(
//                 'member_name',
//                 'agent_id',
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->groupBy('member_name', 'agent_id')
//             ->get();

//         foreach ($memberSummaries as $summary) {
//             DB::table('daily_summaries')->updateOrInsert(
//                 [
//                     'report_date' => $date->format('Y-m-d'),
//                     'member_name' => $summary->member_name,
//                     'agent_id' => $summary->agent_id
//                 ],
//                 [
//                     'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                     'total_payout_amount' => $summary->total_payout_amount,
//                     'total_bet_amount' => $summary->total_bet_amount,
//                     'total_win_amount' => $summary->total_win_amount,
//                     'total_lose_amount' => $summary->total_lose_amount,
//                     'total_stake_count' => $summary->total_stake_count,
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ]
//             );
//         }
//     }

//     private function isExistingAgent($userId)
//     {
//         $user = User::find($userId);

//         return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
//     }

//     private function getAgent()
//     {
//         return $this->isExistingAgent(Auth::id());
//     }

//     private function getAgentChildrenIds($agent, array $hierarchy)
//     {
//         foreach ($hierarchy as $role => $levels) {
//             if ($agent->hasRole($role)) {
//                 return collect([$agent])
//                     ->flatMap(fn($levelAgent) => $this->getChildrenRecursive($levelAgent, $levels))
//                     ->pluck('id')
//                     ->toArray();
//             }
//         }

//         return [];
//     }

//     private function getChildrenRecursive($agent, array $levels)
//     {
//         $children = collect([$agent]);
//         foreach ($levels as $level) {
//             $children = $children->flatMap->children;
//         }

//         return $children->flatMap->children;
//     }
// }


// class DailySummaryController extends Controller
// {
//     protected const SUB_AGENT_ROlE = 'Sub Agent';

//     public function index(Request $request)
//     {
//         $agent = $this->getAgent() ?? Auth::user();

//         $hierarchy = [
//             'Owner' => ['Senior', 'Master', 'Agent'],
//             'Senior' => ['Master', 'Agent'],
//             'Master' => ['Agent'],
//         ];

//         $query = DailySummary::query()
//                 ->join('users', 'users.user_name', '=', 'daily_summaries.member_name')
//                 ->select('daily_summaries.*');
            
//         // Apply date filters if provided
//         if ($request->filled('start_date')) {
//             $query->whereDate('report_date', '>=', Carbon::parse($request->start_date));
//         }

//         if ($request->filled('end_date')) {
//             $query->whereDate('report_date', '<=', Carbon::parse($request->end_date));
//         }
//         if ($agent->hasRole('Senior Owner')) {
//             $result = $query;
//         } elseif ($agent->hasRole('Agent')) {
//             $agentChildrenIds = $agent->children->pluck('id')->toArray();
//             $result = $query->whereIn('users.id', $agentChildrenIds);
//         } else {
//             $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
//             $result = $query->whereIn('users.id', $agentChildrenIds);
//         }

//         $summaries = $result->orderBy('report_date', 'desc')
//             ->paginate(10)
//             ->appends($request->only(['start_date', 'end_date'])); // Preserve query parameters in pagination links

//         return view('admin.daily_summaries.index', compact('summaries'));
//     }

//     public function generateSummaries(Request $request)
//     {
//         $request->validate([
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date'
//         ]);

//         $startDate = Carbon::parse($request->input('start_date'));
//         $endDate = Carbon::parse($request->input('end_date'));

//         // Limit date range to prevent memory issues
//         if ($startDate->diffInDays($endDate) > 31) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Date range cannot exceed 31 days'
//             ], 400);
//         }

//         try {
//             $period = CarbonPeriod::create($startDate, $endDate);
//             $processedDates = [];
//             $totalSummaries = 0;

//             foreach ($period as $date) {
//                 $startOfDay = $date->copy()->startOfDay();
//                 $endOfDay = $date->copy()->endOfDay();

//                 // Generate all summary types
//                 $this->generateOverallSummary($date, $startOfDay, $endOfDay);
//                 $this->generateAgentSummaries($date, $startOfDay, $endOfDay);
//                 $this->generateMemberSummaries($date, $startOfDay, $endOfDay);

//                 $processedDates[] = $date->format('Y-m-d');
//                 $totalSummaries += $this->getSummaryCountForDate($date);
//             }

//             return response()->json([
//                 'success' => true,
//                 'message' => "Daily summaries generated successfully!",
//                 'processed_dates' => $processedDates,
//                 'total_summaries_created' => $totalSummaries,
//                 'date_range' => [
//                     'start' => $startDate->format('Y-m-d'),
//                     'end' => $endDate->format('Y-m-d')
//                 ]
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to generate summaries',
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }

//     protected function getSummaryCountForDate($date)
//     {
//         return DB::table('daily_summaries')
//             ->whereDate('report_date', $date->format('Y-m-d'))
//             ->count();
//     }

//     protected function generateOverallSummary($date, $startOfDay, $endOfDay)
//     {
//         $summary = DB::table('reports')
//             ->select(
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->first();

//         DB::table('daily_summaries')->updateOrInsert(
//             [
//                 'report_date' => $date->format('Y-m-d'),
//                 'member_name' => null,
//                 'agent_id' => null
//             ],
//             [
//                 'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                 'total_payout_amount' => $summary->total_payout_amount,
//                 'total_bet_amount' => $summary->total_bet_amount,
//                 'total_win_amount' => $summary->total_win_amount,
//                 'total_lose_amount' => $summary->total_lose_amount,
//                 'total_stake_count' => $summary->total_stake_count,
//                 'updated_at' => now(),
//             ]
//         );
//     }

//     protected function generateAgentSummaries($date, $startOfDay, $endOfDay)
//     {
//         $agentSummaries = DB::table('reports')
//             ->select(
//                 'agent_id',
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->groupBy('agent_id')
//             ->get();

//         foreach ($agentSummaries as $summary) {
//             DB::table('daily_summaries')->updateOrInsert(
//                 [
//                     'report_date' => $date->format('Y-m-d'),
//                     'member_name' => null,
//                     'agent_id' => $summary->agent_id
//                 ],
//                 [
//                     'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                     'total_payout_amount' => $summary->total_payout_amount,
//                     'total_bet_amount' => $summary->total_bet_amount,
//                     'total_win_amount' => $summary->total_win_amount,
//                     'total_lose_amount' => $summary->total_lose_amount,
//                     'total_stake_count' => $summary->total_stake_count,
//                     'updated_at' => now(),
//                 ]
//             );
//         }
//     }

//     protected function generateMemberSummaries($date, $startOfDay, $endOfDay)
//     {
//         $memberSummaries = DB::table('reports')
//             ->select(
//                 'member_name',
//                 'agent_id',
//                 DB::raw('COALESCE(SUM(valid_bet_amount), 0) as total_valid_bet_amount'),
//                 DB::raw('COALESCE(SUM(payout_amount), 0) as total_payout_amount'),
//                 DB::raw('COALESCE(SUM(bet_amount), 0) as total_bet_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount > bet_amount THEN payout_amount - bet_amount ELSE 0 END), 0) as total_win_amount'),
//                 DB::raw('COALESCE(SUM(CASE WHEN payout_amount < bet_amount THEN bet_amount - payout_amount ELSE 0 END), 0) as total_lose_amount'),
//                 DB::raw('COALESCE(COUNT(*), 0) as total_stake_count')
//             )
//             ->whereBetween('created_on', [$startOfDay, $endOfDay])
//             ->groupBy('member_name', 'agent_id')
//             ->get();

//         foreach ($memberSummaries as $summary) {
//             DB::table('daily_summaries')->updateOrInsert(
//                 [
//                     'report_date' => $date->format('Y-m-d'),
//                     'member_name' => $summary->member_name,
//                     'agent_id' => $summary->agent_id
//                 ],
//                 [
//                     'total_valid_bet_amount' => $summary->total_valid_bet_amount,
//                     'total_payout_amount' => $summary->total_payout_amount,
//                     'total_bet_amount' => $summary->total_bet_amount,
//                     'total_win_amount' => $summary->total_win_amount,
//                     'total_lose_amount' => $summary->total_lose_amount,
//                     'total_stake_count' => $summary->total_stake_count,
//                     'updated_at' => now(),
//                 ]
//             );
//         }
//     }

//     private function isExistingAgent($userId)
//     {
//         $user = User::find($userId);

//         return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
//     }

//     private function getAgent()
//     {
//         return $this->isExistingAgent(Auth::id());
//     }

//     private function getAgentChildrenIds($agent, array $hierarchy)
//     {
//         foreach ($hierarchy as $role => $levels) {
//             if ($agent->hasRole($role)) {
//                 return collect([$agent])
//                     ->flatMap(fn($levelAgent) => $this->getChildrenRecursive($levelAgent, $levels))
//                     ->pluck('id')
//                     ->toArray();
//             }
//         }

//         return [];
//     }

//     private function getChildrenRecursive($agent, array $levels)
//     {
//         $children = collect([$agent]);
//         foreach ($levels as $level) {
//             $children = $children->flatMap->children;
//         }

//         return $children->flatMap->children;
//     }
// }
