<?php

namespace App\Models\Webhook;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $table = 'bets'; // Ensure the table name matches your database

    protected $fillable = [
        'user_id',
        'game_provide_name',
        'game_name',
        'operator_id',
        'request_date_time',
        'signature',
        'player_id',
        'currency',
        'round_id',
        'bet_id',
        'game_code',
        'bet_amount',
        'tran_date_time',
        'auth_token',
        'status',
        'cancelled_at',
    ];

    /**
     * Get the user that owns the bet.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
