<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeamlessEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'product_id',
        'raw_data',
        // 'operator_id',
        // 'provider_id',
        // 'provider_line_id',
        // 'currency_id',
        // 'game_type',
        // 'game_id',
        // 'game_round_id',
        // 'payout_detail',
        // 'commission_amount',
        // 'jackpot_amount',
        // 'operator_code',
        // 'sign',
        // 'jp_bet',
        'request_time',
    ];

    protected $casts = [
        'raw_data' => 'json',
    ];

    public function transactions()
    {
        return $this->hasMany(SeamlessTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Yangon');
            $model->updated_at = Carbon::now('Asia/Yangon');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Yangon');
        });
    }
}
