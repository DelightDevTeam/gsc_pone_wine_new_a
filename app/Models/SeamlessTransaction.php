<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\WagerStatus;
use App\Models\SeamlessEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeamlessTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'seamless_event_id',
        'user_id',
        'game_type_id',
        'product_id',
        'wager_id',
        'valid_bet_amount',
        'bet_amount',
        'transaction_amount',
        'transaction_id',
        'rate',
        'payout_amount',
        //'settlement_date',
        'status',
        'wager_status',
        // 'created_on',
        // 'modified_on',
        'member_name',
        //'request_time',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
        'wager_status' => WagerStatus::class,
        'valid_bet_amount' => 'decimal:2',
        'bet_amount' => 'decimal:2',
        'transaction_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seamlessEvent()
    {
        return $this->belongsTo(SeamlessEvent::class, 'seamless_event_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
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
