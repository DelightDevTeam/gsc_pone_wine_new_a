<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\GameType;
use App\Models\Admin\GameTypeProduct;

class GameTypeProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'product_id' => 1,
                'game_type_id' => 1,
                'image' => 'Pragmatic_Play.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 1,
                'game_type_id' => 2,
                'image' => 'pragmatic_casino.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 2,
                'game_type_id' => 3,
                'image' => 'sbo_sport.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 3,
                'game_type_id' => 1,
                'image' => 'Joker.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 3,
                'game_type_id' => 4,
                'image' => 'Joker.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 4,
                'game_type_id' => 2,
                'image' => 'YEE_BET.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 5,
                'game_type_id' => 2,
                'image' => 'WM.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 6,
                'game_type_id' => 1,
                'image' => 'YGG.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 7,
                'game_type_id' => 1,
                'image' => 'Space_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 7,
                'game_type_id' => 4,
                'image' => 'Space_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 8,
                'game_type_id' => 2,
                'image' => 'vivo_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 9,
                'game_type_id' => 1,
                'image' => 'playstar.jpeg',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 9,
                'game_type_id' => 4,
                'image' => 'vivo_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 10,
                'game_type_id' => 1,
                'image' => 'True_Lap.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 11,
                'game_type_id' => 1,
                'image' => 'Bgaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 12,
                'game_type_id' => 1,
                'image' => 'Wazdan.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 13,
                'game_type_id' => 1,
                'image' => 'Fazi.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 14,
                'game_type_id' => 1,
                'image' => 'play_pearls.jpeg',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 15,
                'game_type_id' => 1,
                'image' => 'Net_Game.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 16,
                'game_type_id' => 5,
                'image' => 'KIRON.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 17,
                'game_type_id' => 1,
                'image' => 'Redrakt.png
                ',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 18,
                'game_type_id' => 1,
                'image' => 'Bcocon.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 19,
                'game_type_id' => 1,
                'image' => 'Sky_Wind.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 20,
                'game_type_id' => 1,
                'image' => 'JDB.png',
                'rate' => '100.0000',
            ],
            [
                'product_id' => 21,
                'game_type_id' => 1,
                'image' => 'Genesis.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 22,
                'game_type_id' => 1,
                'image' => 'FUNTA_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 23,
                'game_type_id' => 1,
                'image' => 'Felix_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 24,
                'game_type_id' => 1,
                'image' => 'Zeus.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 25,
                'game_type_id' => 1,
                'image' => 'KA_Gaming.png',
                'rate' => '1.0000',
            ],

            [
                'product_id' => 26,
                'game_type_id' => 1,
                'image' => 'Netent.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 27,
                'game_type_id' => 1,
                'image' => 'Gaming_World.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 28,
                'game_type_id' => 1,
                'image' => 'Asia_Gaming.png',
                'rate' => '100.0000',
            ],
            [
                'product_id' => 28,
                'game_type_id' => 2,
                'image' => 'Asia_Gaming_Casino.png',
                'rate' => '100.0000',
            ],
            [
                'product_id' => 29,
                'game_type_id' => 2,
                'image' => 'Evolotion.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 30,
                'game_type_id' => 1,
                'image' => 'Big_Gaming.png',
                'rate' => '1000.0000',
            ],
            [
                'product_id' => 30,
                'game_type_id' => 2,
                'image' => 'Big_Gaming.png',
                'rate' => '1000.0000',
            ],
            [
                'product_id' => 31,
                'game_type_id' => 1,
                'image' => 'PG_soft.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 32,
                'game_type_id' => 1,
                'image' => 'CQ9_Slot.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 32,
                'game_type_id' => 4,
                'image' => 'CQ9.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 33,
                'game_type_id' => 2,
                'image' => 'Sexy_gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 34,
                'game_type_id' => 1,
                'image' => 'Real_Time_Gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 35,
                'game_type_id' => 1,
                'image' => 'JILI.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 36,
                'game_type_id' => 2,
                'image' => 'King_855.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 37,
                'game_type_id' => 1,
                'image' => 'Hanbanero.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 38,
                'game_type_id' => 1,
                'image' => 'live22.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 39,
                'game_type_id' => 1,
                'image' => 'yesgetrich.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 40,
                'game_type_id' => 1,
                'image' => 'Simpleplay.png',
                'rate' => '1000.0000',
            ],

            [
                'product_id' => 41,
                'game_type_id' => 1,
                'image' => 'Advant_Play.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 42,
                'game_type_id' => 3,
                'image' => 'S_Sport.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 43,
                'game_type_id' => 2,
                'image' => 'Dream_Gaming.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 44,
                'game_type_id' => 1,
                'image' => 'Mr_Slotty.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 45,
                'game_type_id' => 1,
                'image' => 'evoplay.png',
                'rate' => '1.0000',
            ],
            [
                'product_id' => 46,
                'game_type_id' => 1,
                'image' => 'Smartsoft.png',
                'rate' => '1.0000',
            ],
        ];

        GameTypeProduct::insert($data);
    }
    // public function run(): void
    // {
    //     $data = [
    //         [
    //             'product_id' => 1,  // PPLAY
    //             'game_type_id' => 2,  // Slots
    //             'image' => 'pp_play.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 2,  // PPLAYLIVE
    //             'game_type_id' => 6,  // LiveCasino
    //             'image' => 'pragmatic_casino.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 3,  // PGSOFT
    //             'game_type_id' => 2,  // Slots
    //             'image' => 'pg_soft.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 4,  // JILI
    //             'game_type_id' => 2,  // Slots
    //             'image' => 'jl_slot.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 5,  // L22
    //             'game_type_id' => 2,  // Slots
    //             'image' => 'live_22.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 6,  // JDB
    //             'game_type_id' => 2,  // Other
    //             'image' => 'JDB.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 7,  // CQ9
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'cq_9_slot.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 7,  // CQ9
    //             'game_type_id' => 3,  // fish
    //             'image' => 'cq_9_fish.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 8,  // UUS
    //             'game_type_id' => 2,  // Slots
    //             'image' => 'UUslot.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 9,  // MGH5
    //             'game_type_id' => 2,  // Other
    //             'image' => 'MEGAH5.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 10,  // MGH5
    //             'game_type_id' => 2,  // Other
    //             'image' => 'Epic_win.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 11,  // MGH5
    //             'game_type_id' => 2,  // Other
    //             'image' => 'Yellow_bet.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 12,  // EVOPLAY
    //             'game_type_id' => 2,  // Other
    //             'image' => 'Evo_Play.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 13,  // FACHAI
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'Fachai.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 14,  // FACHAI
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'bng.jfif',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 15,  // FACHAI
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'ygr.jpg',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 16,  // FACHAI
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'hack_saw.jfif',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 17,  // FUNTA
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'funta.png',
    //             'rate' => '1.0000',
    //         ],
    //         [
    //             'product_id' => 18,  // FUNTA
    //             'game_type_id' => 2,  // Arcade
    //             'image' => 'simple_play.png',
    //             'rate' => '1.0000',
    //         ],
    //     ];

    //     DB::table('game_type_product')->insert($data);
    // }
}