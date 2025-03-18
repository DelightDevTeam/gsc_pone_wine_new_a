<?php

namespace App\Models\Admin;

use App\Models\Admin\GameList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'short_name', 'order', 'status', 'game_list_status'];

    protected $appends = ['imgUrl']; // Changed from 'image' to 'imgUrl'
    //protected $appends = ['image'];

    public function gameTypes()
    {
        return $this->belongsToMany(GameType::class)->withPivot('image');
    }

    public function gameLists()
    {
        return $this->hasMany(GameList::class);
    }

    public function getImgUrlAttribute()
    {
        if (isset($this->pivot) && isset($this->pivot->image)) {
            return asset('assets/img/provider_logo/'.$this->pivot->image);
        }

    }
}
