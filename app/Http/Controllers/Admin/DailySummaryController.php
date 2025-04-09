<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Admin\DailySummary;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DailySummaryController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';

    public function index(Request $request)
    {
        $agent = $this->getAgent() ?? Auth::user();

        $hierarchy = [
            'Owner' => ['Senior', 'Master', 'Agent'],
            'Senior' => ['Master', 'Agent'],
            'Master' => ['Agent'],
        ];

        $query = DailySummary::query();

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('report_date', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('report_date', '<=', Carbon::parse($request->end_date));
        }
        if ($agent->hasRole('Senior Owner')) {
            $result = $query;
        } elseif ($agent->hasRole('Agent')) {
            $agentChildrenIds = $agent->children->pluck('id')->toArray();
            $result = $query->whereIn('users.id', $agentChildrenIds);
        } else {
            $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
            $result = $query->whereIn('users.id', $agentChildrenIds);
        }

        $summaries = $result->orderBy('report_date', 'desc')
            ->paginate(10)
            ->appends($request->only(['start_date', 'end_date'])); // Preserve query parameters in pagination links

        return view('admin.daily_summaries.index', compact('summaries'));
    }

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
            $period = CarbonPeriod::create($startDate, $endDate);
            $processedDates = [];
            $totalSummaries = 0;

            foreach ($period as $date) {
                $startOfDay = $date->copy()->startOfDay();
                $endOfDay = $date->copy()->endOfDay();

                // Generate all summary types
                $this->generateOverallSummary($date, $startOfDay, $endOfDay);
                $this->generateAgentSummaries($date, $startOfDay, $endOfDay);
                $this->generateMemberSummaries($date, $startOfDay, $endOfDay);

                $processedDates[] = $date->format('Y-m-d');
                $totalSummaries += $this->getSummaryCountForDate($date);
            }

            return response()->json([
                'success' => true,
                'message' => "Daily summaries generated successfully!",
                'processed_dates' => $processedDates,
                'total_summaries_created' => $totalSummaries,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summaries',
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
            ->get();

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
            ->get();

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
}
