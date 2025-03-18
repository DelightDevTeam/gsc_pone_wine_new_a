<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Telegram', 'image' => 'telegram.png'],
            ['name' => 'Viber', 'image' => 'viber.png']
        ];
        foreach ($types as $type) {
            \App\Models\ContactType::create($type);
        }
    }
}
