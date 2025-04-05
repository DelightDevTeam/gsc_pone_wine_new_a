<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeamlessTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'from_date' => Carbon::parse($this->from_date)->timezone('Asia/Yangon')->format('Y-m-d H:i:s'),
            'to_date' => Carbon::parse($this->to_date)->timezone('Asia/Yangon')->format('Y-m-d H:i:s'),
            'product' => $this->provider_name,
            'total_count' => $this->total_count,
            'total_bet_amount' => number_format($this->total_bet_amount, 2),
            'total_transaction_amount' => number_format($this->net_win, 2),
        ];
    }
}
