<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $gameProviders = [
            [
                'provider_code' => 'PPLAY',
                'provider_name' => 'PragmaticPlay',
                'is_active' => true,
                'order' => 1,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'PPLAYLIVE',
                'provider_name' => 'PragmaticPlay Live',
                'is_active' => true,
                'order' => 2,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'PGSOFT',
                'provider_name' => 'PGSoft',
                'is_active' => true,
                'order' => 3,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'JILI',
                'provider_name' => 'JILI',
                'is_active' => true,
                'order' => 4,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'L22',
                'provider_name' => 'Live22',
                'is_active' => true,
                'order' => 5,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'JDB',
                'provider_name' => 'JDB Gaming',
                'is_active' => true,
                'order' => 6,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'CQ9',
                'provider_name' => 'CQ9',
                'is_active' => true,
                'order' => 7,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'UUS',
                'provider_name' => 'UUSlot',
                'is_active' => true,
                'order' => 8,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'MGH5',
                'provider_name' => 'MegaH5',
                'is_active' => true,
                'order' => 9,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'EPW',
                'provider_name' => 'EpicWin',
                'is_active' => true,
                'order' => 10,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'YBAT',
                'provider_name' => 'YellowBat',
                'is_active' => true,
                'order' => 11,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'EVOPLAY',
                'provider_name' => 'EvoPlay',
                'is_active' => true,
                'order' => 12,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'FACHAI',
                'provider_name' => 'FaChai',
                'is_active' => true,
                'order' => 13,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'BNG',
                'provider_name' => 'BNG',
                'is_active' => true,
                'order' => 14,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'YGR',
                'provider_name' => 'YGR',
                'is_active' => true,
                'order' => 15,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'HSW',
                'provider_name' => 'Hacksaw',
                'is_active' => true,
                'order' => 16,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'FUNTA',
                'provider_name' => 'Funta',
                'is_active' => true,
                'order' => 17,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'SPP',
                'provider_name' => 'SimplePlay',
                'is_active' => true,
                'order' => 18,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'WFG',
                'provider_name' => 'WFGaming',
                'is_active' => true,
                'order' => 19,
                'status' => 1,
                'game_list_status' => 1,
            ],

            [
                'provider_code' => 'SAG',
                'provider_name' => 'SAGaming',
                'is_active' => true,
                'order' => 20,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'GPD',
                'provider_name' => 'GamingPanda',
                'is_active' => true,
                'order' => 21,
                'status' => 1,
                'game_list_status' => 1,
            ],
            [
                'provider_code' => 'R88',
                'provider_name' => 'Rich88',
                'is_active' => true,
                'order' => 22,
                'status' => 1,
                'game_list_status' => 1,
            ],

        ];

        DB::table('products')->insert($gameProviders);
    }
}