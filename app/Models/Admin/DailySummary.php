<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'member_name',
        'agent_id',
        'total_payout_amount',
        'total_bet_amount',
        'total_win_amount',
        'total_lose_amount',
        'total_stake_count'
    ];

    // Cast report_date as a date
    protected $casts = [
        'report_date' => 'date',
        'created_at' => 'datetime',
    ];
}
