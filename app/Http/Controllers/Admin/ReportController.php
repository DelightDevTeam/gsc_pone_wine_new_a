<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\ReportTransaction;

class ReportController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';

    public function ponewine(Request $request)
    {
        $agent = $this->getAgent() ?? Auth::user();

        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        // Define role hierarchy
        $hierarchy = [
            'Owner' => ['Super', 'Senior', 'Master', 'Agent'],
            'Super' => ['Senior', 'Master', 'Agent'],
            'Senior' => ['Master', 'Agent'],
            'Master' => ['Agent'],
        ];

        $playerTotalsQuery = DB::table('pone_wine_player_bets')
            ->select([
                'user_id',
                'user_name',
                DB::raw('SUM(win_lose_amt) as total_win_lose_amt'),
            ])
            ->groupBy('user_id', 'user_name');

        if ($agent->hasRole('Senior Owner')) {
            $playerTotals = $playerTotalsQuery;
        } elseif ($agent->hasRole('Agent')) {
            $agentChildrenIds = $agent->children->pluck('id')->toArray();
            $playerTotals = $playerTotalsQuery->whereIn('user_id', $agentChildrenIds);
        } else {
            $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
            $playerTotals = $playerTotalsQuery->whereIn('user_id', $agentChildrenIds);
        }

        $reports = DB::table('pone_wine_bet_infos')
            ->join('pone_wine_player_bets', 'pone_wine_player_bets.id', '=', 'pone_wine_bet_infos.pone_wine_player_bet_id')
            ->joinSub($playerTotals, 'player_totals', function ($join) {
                $join->on('player_totals.user_id', '=', 'pone_wine_player_bets.user_id');
            })
            ->select([
                'player_totals.user_id',
                'player_totals.user_name',
                'player_totals.total_win_lose_amt',
                DB::raw('SUM(pone_wine_bet_infos.bet_amount) as total_bet_amount'),
            ])
            ->groupBy('player_totals.user_id', 'player_totals.user_name', 'player_totals.total_win_lose_amt')
            ->whereBetween('pone_wine_bet_infos.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->get();

        return view('admin.report.ponewine.index', compact('reports'));
    }

    public function detail($playerId)
    {
        $reports = DB::table('pone_wine_bet_infos')
            ->join('pone_wine_player_bets', 'pone_wine_player_bets.id', '=', 'pone_wine_bet_infos.pone_wine_player_bet_id')
            ->join('pone_wine_bets', 'pone_wine_bets.id', '=', 'pone_wine_player_bets.pone_wine_bet_id')
            ->select([
                'pone_wine_player_bets.user_name',
                'pone_wine_bet_infos.bet_no',
                'pone_wine_bet_infos.bet_amount',
                'pone_wine_bets.win_number',
                'pone_wine_bets.match_id',
            ])
            ->where('pone_wine_player_bets.user_id', $playerId)
            ->get();

        return view('admin.report.ponewine.detail', compact('reports'));
    }

    public function index(Request $request)
    {
        $agent = $this->getAgent() ?? Auth::user();

        $report = $this->buildQuery($request, $agent);

        $totalstake = $report->sum('total_count');
        $totalBetAmt = $report->sum('total_bet_amount');
        $totalWinAmt = $report->sum('total_payout_amount');

        $total = [
            'totalstake' => $totalstake,
            'totalBetAmt' => $totalBetAmt,
            'totalWinAmt' => $totalWinAmt,
        ];

        return view('admin.report.index', compact('report', 'total'));
    }

    public function getReportDetails(Request $request, $playerId)
    {

        $details = $this->getPlayerDetails($playerId, $request);

        $productTypes = Product::where('status', 1)->get();

        return view('admin.report.detail', compact('details', 'productTypes', 'playerId'));
    }

    public function getPlayer($playerId)
    {
        $poneWineReport = DB::table('pone_wine_bets')
            ->join('pone_wine_player_bets', 'pone_wine_player_bets.pone_wine_bet_id', '=', 'pone_wine_bets.id')
            ->join('pone_wine_bet_infos', 'pone_wine_bet_infos.pone_wine_player_bet_id', '=', 'pone_wine_player_bets.id')
            ->select([
                'pone_wine_player_bets.win_lose_amt',
                'pone_wine_player_bets.user_name',
                DB::raw('SUM(pone_wine_bet_infos.bet_amount) as total_bet_amount'),
                'pone_wine_bets.win_number',
                'pone_wine_bets.match_id',
            ])
            ->where('pone_wine_player_bets.user_id', $playerId)
            ->groupBy([
                'pone_wine_player_bets.user_name',
                'pone_wine_bets.win_number',
                'pone_wine_bets.match_id',
                'pone_wine_player_bets.win_lose_amt',
            ])
            ->get();

        $combinedSubquery = DB::table('results')
            ->select(
                'user_id',
                DB::raw('MIN(tran_date_time) as from_date'),
                DB::raw('MAX(tran_date_time) as to_date'),
                DB::raw('COUNT(results.game_code) as total_count'),
                DB::raw('SUM(total_bet_amount) as total_bet_amount'),
                DB::raw('SUM(win_amount) as win_amount'),
                DB::raw('SUM(net_win) as net_win'),
                'products.provider_name'
            )
            ->join('game_lists', 'game_lists.game_id', '=', 'results.game_code')
            ->join('products', 'products.id', '=', 'game_lists.product_id')
            ->groupBy('products.provider_name', 'user_id')
            ->unionAll(
                DB::table('bet_n_results')
                    ->select(
                        'user_id',
                        DB::raw('MIN(tran_date_time) as from_date'),
                        DB::raw('MAX(tran_date_time) as to_date'),
                        DB::raw('COUNT(bet_n_results.game_code) as total_count'),
                        DB::raw('SUM(bet_amount) as total_bet_amount'),
                        DB::raw('SUM(win_amount) as win_amountwin_amount'),
                        DB::raw('SUM(net_win) as net_win'),
                        'products.provider_name'
                    )
                    ->join('game_lists', 'game_lists.game_id', '=', 'bet_n_results.game_code')
                    ->join('products', 'products.id', '=', 'game_lists.product_id')
                    ->groupBy('products.provider_name', 'user_id')
            );

        $slotReports = DB::table('users as players')
            ->joinSub($combinedSubquery, 'combined', 'combined.user_id', '=', 'players.id')
            ->where('players.id', $playerId)
            ->orderBy('players.id', 'desc')
            ->get();

        return view('admin.report.player.index', compact('poneWineReport', 'slotReports'));
    }

    public function shanReportIndex(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        $owner = auth()->user();

        $reportData = $this->getShanReportQuery($owner,$startDate,$endDate);

        $filteredReports =  $reportData->paginate(50);

        // dd($filteredReports);



        // $user_id = Auth::id();

        // $userTransactions = ReportTransaction::where('user_id', $user_id)
        //     ->orderByDesc('created_at')
        //     ->get();

        // // Get player name
        // $player = Auth::user();
        // $playerName = $player ? $player->user_name : 'Unknown';




        // $totalBet = $userTransactions->sum('bet_amount');

        // $totalWin = $userTransactions->where('win_lose_status', 1)->sum('transaction_amount');

        // // Calculate Total Lose Amount (win_lose_status = 0)
        // $totalLose = $userTransactions->where('win_lose_status', 0)
        //     ->sum(function ($transaction) {
        //         return abs($transaction->transaction_amount);
        //     });

        // // Format the response data
        // $data = [
        //     'user_id' => $user_id,
        //     'player_name' => $playerName,
        //     'total_bet' => $totalBet,
        //     'total_win' => $totalWin,
        //     'total_lose' => $totalLose,
        //     'transactions' => $userTransactions,
        // ];

        return view('admin.report.shan.index',compact('filteredReports'));
    }

    public function shanReportDetail($id) {
        $owner = auth()->user();

        // if($owner->hasRole('Senior Owner')) {
        $reportData = DB::table('users as o')
            ->join('users as s', 's.agent_id', '=', 'o.id')          // senior
            ->join('users as m', 'm.agent_id', '=', 's.id')          // master
            ->join('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->join('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('report_transactions', 'report_transactions.user_id', '=', 'p.id')
            ->where('o.id', $owner->id)
            ->groupBy('o.id', 'p.id', 'p.name', 'p.user_name')
            ->selectRaw('
                o.id as owner_id,
                s.id as senior_id
                m.id as master_id
                a.id as agent_id
                p.id as player_id,
                p.name as player_name,
                p.user_name as player_username,
                SUM(report_transactions.bet_amount) as bet_amount,
                SUM(report_transactions.transaction_amount) as transaction_amount
            ')
            ->get();

        dd($reportData);
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

    private function buildQuery(Request $request, $agent)
    {
        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        $hierarchy = [
            'Owner' => ['Senior', 'Master', 'Agent'],
            'Senior' => ['Master', 'Agent'],
            'Master' => ['Agent'],
        ];

        $query = DB::table('reports')
            ->select(
                'users.id as user_id',
                'users.name as name',
                'users.user_name as user_name',
                DB::raw('count(reports.product_code) as total_count'),
                DB::raw('SUM(reports.bet_amount) as total_bet_amount'),
                DB::raw('SUM(reports.payout_amount) as total_payout_amount'),
                DB::raw('MAX(wallets.balance) as balance'),
            )
            ->leftjoin('users', 'reports.member_name', '=', 'users.user_name')
            ->leftJoin('wallets', 'wallets.holder_id', '=', 'users.id')
            ->when($request->player_id, fn ($query) => $query->where('users.user_name', $request->player_id))
            ->whereBetween('reports.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($agent->hasRole('Senior Owner')) {
            $result = $query;
        } elseif ($agent->hasRole('Agent')) {
            $agentChildrenIds = $agent->children->pluck('id')->toArray();
            $result = $query->whereIn('users.id', $agentChildrenIds);
        } else {
            $agentChildrenIds = $this->getAgentChildrenIds($agent, $hierarchy);
            $result = $query->whereIn('users.id', $agentChildrenIds);
        }

        return $result->groupBy('users.id', 'users.name', 'users.user_name')->get();
    }

    private function getPlayerDetails($playerId, $request)
    {
        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();
        $query = DB::table('reports')
            ->join('products', 'products.code', '=', 'reports.product_code')
            ->where('member_name', $playerId)
            ->when($request->product_id, fn ($query) => $query->where('products.id', $request->product_id));

        return $query->orderBy('created_on', 'desc')->get();
    }

    private function getAgentChildrenIds($agent, array $hierarchy)
    {
        foreach ($hierarchy as $role => $levels) {
            if ($agent->hasRole($role)) {
                return collect([$agent])
                    ->flatMap(fn ($levelAgent) => $this->getChildrenRecursive($levelAgent, $levels))
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

    private function getShanReportQuery ($owner,$startDate,$endDate) {
        $query = DB::table('users as so')
            ->Leftjoin('users as o', 'o.agent_id', '=', 'so.id')
            ->Leftjoin('users as s', 's.agent_id', '=', 'o.id')          // senior
            ->Leftjoin('users as m', 'm.agent_id', '=', 's.id')          // master
            ->Leftjoin('users as a', 'a.agent_id', '=', 'm.id')          // agent
            ->Leftjoin('users as p', 'p.agent_id', '=', 'a.id')          // player
            ->join('report_transactions as rt', 'rt.user_id', '=', 'p.id')
            ->orderBy('rt.created_at', 'desc')
            ->whereBetween('rt.created_at',[$startDate .' 00:00:00', $endDate .' 23:59:59']);
            if($owner->hasRole('Senior Owner')){
                $query->where('so.id', $owner->id)
                ->selectRaw('
                o.user_name as owner_id,
                s.user_name as senior_id,
                m.user_name as master_id,
                a.user_name as agent_id,
                p.user_name as player_id,
                p.name as player_name,
                rt.id as transaction_id,
                rt.bet_amount,
                rt.transaction_amount,
                rt.created_at as transaction_date
            ');
            } elseif($owner->hasRole('Owner')) {
                $query->whereNotNull('o.id')
                ->where('o.id', $owner->id)
                ->selectRaw('
                s.user_name as senior_id,
                m.user_name as master_id,
                a.user_name as agent_id,
                p.user_name as player_id,
                p.name as player_name,
                rt.id as transaction_id,
                rt.bet_amount,
                rt.transaction_amount,
                rt.created_at as transaction_date
            ');
            } elseif($owner->hasRole('Senior')) {
                $query->whereNotNull('s.id')
                ->where('s.id', $owner->id)
                ->selectRaw('
                m.user_name as master_id,
                a.user_name as agent_id,
                p.user_name as player_id,
                p.name as player_name,
                rt.id as transaction_id,
                rt.bet_amount,
                rt.transaction_amount,
                rt.created_at as transaction_date
            ');
            } elseif($owner->hasRole('Master')) {
                $query->whereNotNull('m.id')
                ->where('m.id', $owner->id)
                ->selectRaw('
                a.user_name as agent_id,
                p.user_name as player_id,
                p.name as player_name,
                rt.id as transaction_id,
                rt.bet_amount,
                rt.transaction_amount,
                rt.created_at as transaction_date
            ');
            }  elseif($owner->hasRole('Agent')) {
                $query->whereNotNull('a.id')
                ->where('a.id', $owner->id)
                ->selectRaw('
                p.user_name as player_id,
                p.name as player_name,
                rt.id as transaction_id,
                rt.bet_amount,
                rt.transaction_amount,
                rt.created_at as transaction_date
            ');
            }

            return $query;
    }
}
