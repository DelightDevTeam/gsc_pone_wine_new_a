<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_name', 'room_id', 'bet_no', 'bet_amount', 'win_lose', 'net_win', 'is_winner', 'status'];
}
