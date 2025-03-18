<?php

namespace Database\Seeders;

use App\Models\Admin\GameList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PGSoftGameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load the JSON file
        $json = File::get(base_path('database/seeders/data/PGSOFT.json'));
        $data = json_decode($json, true);

        // Loop through each game in the JSON data
        foreach ($data['Game'] as $game) {
            GameList::create([
                'game_id' => $game['GameId'],
                'game_type_id' => 2,  // Fixed value for game_type_id
                'product_id' => 3,  // Fixed value for product_id
                'status' => 1,  // Default value for status
                'hot_status' => 0,  // Default value for hot_status
                'game_code' => $game['GameCode'],
                'game_name' => $game['GameName'],
                'game_type' => $game['GameType'],
                'image_url' => $game['ImageUrl'],
                'method' => $game['Method'],
                'is_h5_support' => $game['IsH5Support'],
                'maintenance' => $game['Maintenance'] ?? null,
                'game_lobby_config' => $game['GameLobbyConfig'] ?? null,
                'other_name' => json_encode($game['OtherName'] ?? []),
                'has_demo' => $game['HasDemo'],
                'sequence' => $game['Sequence'],
                'game_event' => $game['GameEvent'] ?? null,
                'game_provide_code' => $game['GameProvideCode'],
                'game_provide_name' => $game['GameProvideName'],
            ]);
        }
    }
}
