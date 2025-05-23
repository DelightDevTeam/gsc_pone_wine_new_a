<?php

namespace App\Http\Controllers;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\Admin\UserLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private const PLAYER_ROLE = 7;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->pluck('title');
        // $totalAgent = $user->children->count();
        // $totalPlayer = $user->children->count();
        $totalWinlose = 0;
        $todayWinlose = 0;
        $todayDeposit = 0;
        $todayWithdraw = 0;

        $totalBalance = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('wallets', 'wallets.holder_id', '=', 'users.id')
            ->when($role[0] === 'Senior', function ($query) {
                return $query->where('users.agent_id', Auth::id());
            })
            ->when($role[0] === 'Owner', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->when($role[0] === 'Super', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->when($role[0] === 'Senior', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->when($role[0] === 'Master', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->when($role[0] === 'Agent', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->when($role[0] === 'Sub Agent', function ($query) use ($user) {
                return $query->where('users.agent_id', $user->id);
            })
            ->select(DB::raw('SUM(wallets.balance) as balance'))
            ->first();

        if ($role[0] === 'Agent') {
            $todayWinlose = $this->getWinLose($user->id, true);
            $totalWinlose = $this->getWinLose($user->id);
            $todayDeposit = $this->fetchTotalTransactions($user->id, 'deposit');
            $todayWithdraw = $this->fetchTotalTransactions($user->id, 'withdraw');
        }

        // dd($todayWinlose, $totalWinlose);

        $playerBalance = DB::table('users')
            ->join('wallets', 'wallets.holder_id', '=', 'users.id')
            ->when($role[0] === 'Senior', function ($query) {
                return $query->where('users.type', 40);
            })
            ->first();

        $userCountGet = $this->userCountGet($user);

        $totalSenior = $userCountGet['totalSenior'] ?? 0;
        $totalMaster = $userCountGet['totalMaster'] ?? 0;
        $totalAgent = $userCountGet['totalAgent'] ?? 0;
        $totalPlayer = $userCountGet['totalPlayer'] ?? 0;

        return view('admin.dashboard', compact(
            'user',
            'totalBalance',
            'role',
            'playerBalance',
            'totalSenior',
            'totalMaster',
            'totalAgent',
            'totalPlayer',
            'totalWinlose',
            'todayWinlose',
            'todayDeposit',
            'todayWithdraw'
        ));
    }

    public function balanceUp(Request $request)
    {
        abort_if(
            Gate::denies('senior_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot Access this page because you do not have permission'
        );

        $request->validate([
            'balance' => 'required|numeric',
        ]);

        // Get the current user (admin)
        $admin = Auth::user();

        // Get the current balance before the update
        $openingBalance = $admin->wallet->balanceFloat;

        // Update the balance using the WalletService
        app(WalletService::class)->deposit($admin, $request->balance, TransactionName::CapitalDeposit);

        // Record the transaction in the transactions table
        Transaction::create([
            'payable_type' => get_class($admin),
            'payable_id' => $admin->id,
            'wallet_id' => $admin->wallet->id,
            'type' => 'deposit',
            'amount' => $request->balance,
            'confirmed' => true,
            'meta' => json_encode([
                'name' => TransactionName::CapitalDeposit,
                'opening_balance' => $openingBalance,
                'new_balance' => $admin->wallet->balanceFloat,
                'target_user_id' => $admin->id,
            ]),
            'uuid' => Str::uuid()->toString(),
        ]);

        return back()->with('success', 'Add New Balance Successfully.');
    }

    public function changePassword(Request $request, User $user)
    {
        return view('admin.change_password', compact('user'));
    }

    public function changePlayerSite(Request $request, User $user)
    {
        return view('admin.change_player_site', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('home')->with('success', 'Password has been changed Successfully.');
    }

    public function updatePlayerSiteLink(Request $request, User $user)
    {
        $request->validate([
            'site_link' => 'required|string',
        ]);
        $user->update([
            'site_link' => $request->site_link,
        ]);

        return redirect()->route('home')->with('success', 'Player Site Link has been changed Successfully.');
    }

    public function logs($id)
    {
        $logs = UserLog::with('user')->where('user_id', $id)->get();

        return view('admin.logs', compact('logs'));
    }

    public function playerList()
    {
        $user = Auth::user();
        $role = $user->roles->pluck('title');
        $users = User::where('type', UserType::Player)
            ->when($role[0] === 'Agent', function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->get();

        return view('admin.player_list', compact('users'));
    }

    // private function getTodayWinlose()
    // {
    //     return User::withSum([
    //         'poneWinePlayer as pone_wine_player_win_lose_amt' => function ($query) {
    //             $query->whereDate('created_at', today());
    //         },
    //     ], 'win_lose_amt')
    //         ->withSum([
    //             'results as results_net_win' => function ($query) {
    //                 $query->whereDate('created_at', today());
    //             },
    //         ], 'net_win')
    //         ->withSum([
    //             'betNResults as bet_n_results_net_win' => function ($query) {
    //                 $query->whereDate('created_at', today());
    //             },
    //         ], 'net_win')
    //         ->whereHas('roles', function ($query) {
    //             $query->where('role_id', self::PLAYER_ROLE);
    //         })
    //         ->where('agent_id', Auth::id())
    //         ->get()
    //         ->sum(fn ($user) => ($user->pone_wine_player_win_lose_amt ?? 0)
    //             + ($user->results_net_win ?? 0)
    //             + ($user->bet_n_results_net_win ?? 0));
    // }

    // private function getTotalWinlose()
    // {
    //     return User::withSum('poneWinePlayer as pone_wine_player_win_lose_amt', 'win_lose_amt')
    //         ->withSum('results as results_net_win', 'net_win')
    //         ->withSum('betNResults as bet_n_results_net_win', 'net_win')
    //         ->whereHas('roles', function ($query) {
    //             $query->where('role_id', self::PLAYER_ROLE);
    //         })
    //         ->where('agent_id', Auth::id())
    //         ->get()
    //         ->sum(fn ($user) => $user->pone_wine_player_win_lose_amt
    //             + $user->results_net_win
    //             + $user->bet_n_results_net_win);
    // }

    // private function fetchTotalAmount(User $agent, string $type): float
    // {
    //     return $agent->transactions()
    //         ->with('targetUser')
    //         ->where('type', $type)
    //         ->whereDate('created_at', today())
    //         ->whereIn('name', ['credit_transfer', 'debit_transfer'])
    //         ->sum('amount');
    // }

    // updated by KS
    private function fetchTotalTransactions($id, string $type)
    {
        $data = Transaction::where('target_user_id', $id)->where('type', $type)->where('confirmed', 1)->whereDate('created_at', today())->sum('amount');

        return $data / 100;
    }

    // updated by KS
    private function userCountGet($user)
    {
        $totalSenior = $totalMaster = $totalAgent = $totalPlayer = 0;

        if ($user->hasRole('Owner')) {

            $totalSenior = User::where('agent_id', $user->id)->where('type', 40)->pluck('id');
            $totalMaster = User::whereIn('agent_id', $totalSenior)->where('type', 50)->pluck('id');
            $totalAgent = User::whereIn('agent_id', $totalMaster)->where('type', 60)->pluck('id');
            $totalPlayer = User::whereIn('agent_id', $totalAgent)->count();

            $totalSenior = $totalSenior->count();
            $totalMaster = $totalMaster->count();
            $totalAgent = $totalAgent->count();
        } elseif ($user->hasRole('Senior')) {
            $totalMaster = User::where('agent_id', $user->id)->where('type', 50)->pluck('id');
            $totalAgent = User::whereIn('agent_id', $totalMaster)->where('type', 60)->pluck('id');
            $totalPlayer = User::whereIn('agent_id', $totalAgent)->count();

            $totalMaster = $totalMaster->count();
            $totalAgent = $totalAgent->count();
        } elseif ($user->hasRole('Master')) {
            $totalAgent = User::where('agent_id', $user->id)->where('type', 60)->pluck('id');
            $totalPlayer = User::whereIn('agent_id', $totalAgent)->count();

            $totalAgent = $totalAgent->count();
        } elseif ($user->hasRole('Agent')) {
            $totalPlayer = User::where('agent_id', $user->id)->where('type', 70)->count();
        }

        return compact('totalSenior', 'totalMaster', 'totalAgent', 'totalPlayer');
    }

    // updated by KS
    private function getWinLose($id, $todayOnly = false)
    {
        $query = DB::table('reports')
            // ->join('pone_wine_player_bets', 'pone_wine_player_bets.user_name', '=', 'reports.member_name')
            ->select(
                DB::raw('SUM(reports.bet_amount) as total_bet_amount'),
                DB::raw('SUM(reports.payout_amount) as total_payout_amount'),
                // DB::raw('SUM(pone_wine_player_bets.win_lose_amt) as total_pone_wine_net_win')
            )
            ->where('reports.agent_id', $id);

        if ($todayOnly == true) {
            $startDate = Carbon::today()->startOfDay()->toDateString();
            $endDate = Carbon::today()->endOfDay()->toDateString();
            $query->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
            // dd(true);
        }

        $reportDetail = $query->first();

        return $reportDetail->total_bet_amount - $reportDetail->total_payout_amount;
    }
}
