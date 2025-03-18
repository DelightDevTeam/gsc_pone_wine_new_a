<?php

namespace App\Models\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetresultBackup extends Model
{
    use HasFactory;

    protected $table = 'betresult_backups';

    protected $fillable = [
        'user_id',
        'operator_id',
        'request_date_time',
        'signature',
        'player_id',
        'currency',
        'tran_id',
        'game_code',
        'bet_amount',
        'win_amount',
        'net_win',
        'tran_date_time',
        'provider_code',
        'auth_token',
        'status',
        'cancelled_at',
        'old_balance',
        'new_balance',
    ];

    protected $dates = ['created_at', 'updated_at'];

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
