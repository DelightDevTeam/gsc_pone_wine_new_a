<?php

namespace App\Models\Webhook;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operator_id',
        'request_date_time',
        'signature',
        'player_id',
        'currency',
        'tran_id',
        'reward_id',
        'reward_name',
        'amount',
        'tran_date_time',
        'reward_detail',
    ];

    /**
     * Get the user that owns the reward.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
