<?php

namespace App\Models\Webhook;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
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
        'amount',
        'tran_date_time',
        'remark',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
