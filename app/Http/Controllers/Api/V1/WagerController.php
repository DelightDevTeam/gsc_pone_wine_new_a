<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeamlessTransactionResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WagerController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $type = $request->get('type');

        [$from, $to] = match ($type) {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            default => [now()->startOfDay(), now()],
        };

        $user = auth()->user();
        
        $transactions = DB::table('reports')
            ->select(
                DB::raw('MIN(reports.updated_at) as from_date'),
                DB::raw('MAX(reports.updated_at) as to_date'),
                DB::raw('COUNT(reports.product_code) as total_count'),
                DB::raw('SUM(reports.bet_amount) as total_bet_amount'),
                DB::raw('SUM(reports.payout_amount) as total_payout_amount'),
                'products.name as provider_name',
                'products.code as code'
            )
            ->join('products', 'products.code', '=' , 'reports.product_code')
            ->where('reports.member_name', $user->user_name)
            ->whereBetween('reports.updated_at', [$from, $to])
            ->groupBy('products.code', 'products.name')
            ->get();

        return $this->success(SeamlessTransactionResource::collection($transactions));
    }
}
