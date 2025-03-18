<?php

namespace App\Http\Resources\Slot;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_id' => $this->game_id,
            'game_type_id' => $this->game_type_id,
            'product_id' => $this->product_id,
            'status' => $this->status,
            'hot_status' => $this->hot_status,
            'game_code' => $this->game_code,
            'game_name' => $this->game_name,
            'game_type' => $this->game_type,
            'image_url' => $this->image_url,
            'method' => $this->method,
            'is_h5_support' => $this->is_h5_support,
            'maintenance' => $this->maintenance,
            'game_lobby_config' => $this->game_lobby_config,
            'other_name' => $this->other_name,
            'has_demo' => $this->has_demo,
            'sequence' => $this->sequence,
            'game_event' => $this->game_event,
            'game_provide_code' => $this->game_provide_code,
            'game_provide_name' => $this->game_provide_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'pp_hot' => $this->pp_hot,
        ];
    }
}
