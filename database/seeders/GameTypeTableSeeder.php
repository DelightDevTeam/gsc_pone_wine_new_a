<?php

namespace Database\Seeders;

use App\Models\Admin\GameType;
use Illuminate\Database\Seeder;

class GameTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Slot',
                'name_mm' => 'စလော့',
                'code' => '1',
                'order' => '1',
                'status' => 1,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Live Casino',
                'name_mm' => 'တိုက်ရိုက်ကာစီနို',
                'code' => '2',
                'order' => '2',
                'status' => 1,
                'img' => 'live_casino.png',
            ],
            [
                'name' => 'Sport Book',
                'name_mm' => 'အားကစား',
                'code' => '3',
                'order' => '3',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'Virtual Sport ',
                'name_mm' => 'အားကစား',
                'code' => '4',
                'order' => '4',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'Lottery',
                'name_mm' => 'ထီ',
                'code' => '5',
                'order' => '5',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'Qipai',
                'name_mm' => 'Qipai',
                'code' => '6',
                'order' => '6',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'P2P',
                'name_mm' => 'အားကစား',
                'code' => '7',
                'order' => '7',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'Fishing',
                'name_mm' => 'ငါးဖမ်းခြင်း',
                'code' => '8',
                'order' => '8',
                'status' => 1,
                'img' => 'fishing.png',
            ],

            [
                'name' => 'Other',
                'name_mm' => 'အခြားဂိမ်းများ',
                'code' => '9',
                'order' => '9',
                'status' => 0,
                'img' => 'other.png',
            ],

            [
                'name' => 'Cock Fighting',
                'name_mm' => 'အခြားဂိမ်းများ',
                'code' => '10',
                'order' => '10',
                'status' => 0,
                'img' => 'other.png',
            ],

            [
                'name' => 'Bonus',
                'name_mm' => 'အခြားဂိမ်းများ',
                'code' => '11',
                'order' => '11',
                'status' => 0,
                'img' => 'other.png',
            ],
            [
                'name' => 'Jackpot',
                'name_mm' => 'အားကစား',
                'code' => '12',
                'order' => '12',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
            [
                'name' => 'ESport',
                'name_mm' => 'အားကစား',
                'code' => '13',
                'order' => '13',
                'status' => 1,
                'img' => 'sportbook.png',
            ],
        ];

        GameType::insert($data);
    }
    // public function run(): void
    // {
    //     $data = [
    //         [
    //             'name' => 'Other',
    //             'code' => '0',
    //             'order' => '1',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Slots',
    //             'code' => '1',
    //             'order' => '2',
    //             'status' => 1,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Fish',
    //             'code' => '2',
    //             'order' => '3',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Arcade',
    //             'code' => '3',
    //             'order' => '4',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Table',
    //             'code' => '4',
    //             'order' => '5',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'LiveCasino',
    //             'code' => '5',
    //             'order' => '6',
    //             'status' => 1,
    //             'img' => 'live_casino.png',
    //         ],
    //         [
    //             'name' => 'Crash',
    //             'code' => '6',
    //             'order' => '7',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Lottery',
    //             'code' => '7',
    //             'order' => '8',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],
    //         [
    //             'name' => 'Bingo',
    //             'code' => '8',
    //             'order' => '9',
    //             'status' => 0,
    //             'img' => 'slots.png',
    //         ],

    //     ];

    //     GameType::insert($data);
    // }
}
