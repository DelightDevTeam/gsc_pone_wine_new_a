<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary_date',
        'currency_code',
        'turnover',
        'valid_turnover',
        'payout',
        'win_lose',
    ];
}
