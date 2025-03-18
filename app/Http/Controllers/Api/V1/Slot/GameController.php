<?php

namespace App\Http\Controllers\Api\V1\Slot;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameListResource;
use App\Http\Resources\Slot\GameDetailResource;
use App\Http\Resources\Slot\HotGameListResource;
use App\Models\Admin\GameList;
use App\Models\Admin\GameType;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class GameController extends Controller
{
    use HttpResponses;

    public function gameType()
    {
        $gameType = GameType::where('status', 1)->get();

        return $this->success($gameType);
    }

    public function gameTypeProducts($gameTypeID)
    {
        $gameTypes = GameType::with(['products' => function ($query) {
            $query->where('status', 1);
            $query->orderBy('order', 'asc');
        }])->where('id', $gameTypeID)->where('status', 1)
            ->first();

        return $this->success($gameTypes);
    }

    public function allGameProducts()
    {
        $gameTypes = GameType::with(['products' => function ($query) {
            $query->where('status', 1);
            $query->orderBy('order', 'asc');
        }])->where('status', 1)
            ->get();

        return $this->success($gameTypes);
    }

    public function gameList($product_id, $game_type_id, Request $request)
    {
        $gameLists = GameList::with('product')
            ->where('product_id', $product_id)
            ->where('game_type_id', $game_type_id)
            ->where('status', 1)
            ->OrderBy('order', 'asc')
            ->where('game_name', 'like', '%'.$request->name.'%')
            ->get();

        return $this->success(GameDetailResource::collection($gameLists), 'Game Detail Successfully');
    }

    public function JILIgameList($product_id, $game_type_id, Request $request)
    {
        $gameLists = GameList::with('product')
            ->where('product_id', $product_id)
            ->where('game_type_id', $game_type_id)
            ->where('status', 3)
            ->OrderBy('order', 'asc')
            ->where('game_name', 'like', '%'.$request->name.'%')
            ->get();

        return $this->success(GameDetailResource::collection($gameLists), 'Game Detail Successfully');
    }



    public function getGameDetail($provider_id, $game_type_id)
    {
        $gameLists = GameList::where('provider_id', $provider_id)
            ->where('game_type_id', $game_type_id)->get();

        return $this->success(GameDetailResource::collection($gameLists), 'Game Detail Successfully');
    }

    public function HotgameList()
    {
        $gameLists = GameList::where('hot_status', 1)
            ->get();

        return $this->success(GameDetailResource::collection($gameLists), 'Hot Game Detail Successfully');
    }

    public function PPHotgameList()
    {
        $gameLists = GameList::where('pp_hot', 1)
            ->get();

        return $this->success(GameDetailResource::collection($gameLists), 'Hot Game Detail Successfully');
    }

    public function gameListTest($product_id, $game_type_id, Request $request)
    {
        $gameLists = GameList::with('product')
            ->where('product_id', $product_id)
            ->where('game_type_id', $game_type_id)
            ->where('status', 1)
            ->where('game_name', 'like', '%'.$request->name.'%')
            ->paginate(24);

        return $this->success($gameLists, 'GameList Successfully');
    }
}