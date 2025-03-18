<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::findOrFail(1)->roles()->sync(1); // senior
        User::findOrFail(2)->roles()->sync(2); // owner
        User::findOrFail(3)->roles()->sync(3); // agent
        User::findOrFail(4)->roles()->sync(4); // player
        User::findOrFail(5)->roles()->sync(5); // system wallet
    }
}
