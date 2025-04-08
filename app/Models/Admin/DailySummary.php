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

    protected $casts = [
        'report_date' => 'date',
        'created_at' => 'datetime',
    ];

    // Add a custom accessor for report_date
    public function getReportDateFormattedAttribute()
    {
        return $this->report_date ? $this->report_date->format('Y-m-d') : 'N/A';
    }

    // Add a custom accessor for created_at
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : 'N/A';
    }
}