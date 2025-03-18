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
                'name' => 'Other',
                'code' => '0',
                'order' => '1',
                'status' => 0,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Slots',
                'code' => '1',
                'order' => '2',
                'status' => 1,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Fish',
                'code' => '2',
                'order' => '3',
                'status' => 0,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Arcade',
                'code' => '3',
                'order' => '4',
                'status' => 1,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Table',
                'code' => '4',
                'order' => '5',
                'status' => 1,
                'img' => 'slots.png',
            ],
            [
                'name' => 'LiveCasino',
                'code' => '5',
                'order' => '6',
                'status' => 1,
                'img' => 'live_casino.png',
            ],
            [
                'name' => 'Crash',
                'code' => '6',
                'order' => '7',
                'status' => 0,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Lottery',
                'code' => '7',
                'order' => '8',
                'status' => 1,
                'img' => 'slots.png',
            ],
            [
                'name' => 'Bingo',
                'code' => '8',
                'order' => '9',
                'status' => 1,
                'img' => 'slots.png',
            ],

        ];

        GameType::insert($data);
    }
}