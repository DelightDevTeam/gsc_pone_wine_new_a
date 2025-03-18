<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $data = DB::table('pone_wine_bets')
            ->join('pone_wine_player_bets', 'pone_wine_player_bets.pone_wine_bet_id', '=', 'pone_wine_bets.id')
            ->join('pone_wine_bet_infos', 'pone_wine_bet_infos.pone_wine_player_bet_id', '=', 'pone_wine_player_bets.id')
            ->select([
                'pone_wine_player_bets.win_lose_amt',
                'pone_wine_player_bets.user_name',
                DB::raw('SUM(pone_wine_bet_infos.bet_amount) as total_bet_amount'),
                'pone_wine_bets.win_number',
                'pone_wine_bets.match_id',
            ])
            ->where('pone_wine_player_bets.user_id', Auth::id())
            ->groupBy([
                'pone_wine_player_bets.user_name',
                'pone_wine_bets.win_number',
                'pone_wine_bets.match_id',
                'pone_wine_player_bets.win_lose_amt'
            ])
            ->get();

        return $this->success($data, 'Player Report ');
    }
}
