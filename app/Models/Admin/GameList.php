<?php

namespace App\Models\Admin;

use App\Models\Admin\GameType;
use App\Models\Admin\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameList extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'game_id',
    //     'game_type_id',
    //     'product_id',
    //     'status',
    //     'hot_status',
    //     'game_code',
    //     'game_name',
    //     'game_type',
    //     'image_url',
    //     'method',
    //     'is_h5_support',
    //     'maintenance',
    //     'game_lobby_config',
    //     'other_name',
    //     'has_demo',
    //     'sequence',
    //     'game_event',
    //     'game_provide_code',
    //     'game_provide_name',
    //     'order',
    // ];

    protected $fillable = ['game_type_id', 'product_id', 'name', 'code', 'image_url', 'click_count', 'status', 'hot_status'];

    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    // public function gameType()
    // {
    //     return $this->belongsTo(GameType::class);
    // }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function getImgUrlAttribute()
    {
        return asset('/game_logo/'.$this->image);
    }

    public function scopeHotGame($query)
    {
        return $this->where('status', 1)->where('hot_status', 1);
    }
}
