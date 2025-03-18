<?php

namespace App\Models\Webhook;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $table = 'results'; // Ensure the table name matches your database

    protected $fillable = [
        'user_id',
        'player_name',
        'game_provide_name',
        'game_name',
        'operator_id',
        'game_provide_name',
        'game_name',
        'request_date_time',
        'signature',
        'player_id',
        'currency',
        'round_id',
        'bet_ids',
        'result_id',
        'game_code',
        'total_bet_amount',
        'win_amount',
        'net_win',
        'tran_date_time',
        'old_balance',
        'new_balance',
    ];

    protected $casts = [
        'bet_ids' => 'array', // Cast to array for JSON
    ];

    /**
     * Get the user associated with the result.
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
