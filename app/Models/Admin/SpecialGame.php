<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\GameType;
use App\Models\Admin\Product;

class SpecialGame extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'click_count', 'game_type_id', 'product_id', 'image_url', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }
}
