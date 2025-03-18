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
        User::findOrFail(1)->roles()->sync(1); // senior owner
        User::findOrFail(2)->roles()->sync(2); // owner
        User::findOrFail(3)->roles()->sync(3); // super
        User::findOrFail(4)->roles()->sync(4); // senior
        User::findOrFail(5)->roles()->sync(5); // master
        User::findOrFail(6)->roles()->sync(6); // agent
        User::findOrFail(7)->roles()->sync(7); // player
        User::findOrFail(8)->roles()->sync(8); // system wallet
        User::findOrFail(9)->roles()->sync(7); // player
        User::findOrFail(10)->roles()->sync(7); // player
        User::findOrFail(11)->roles()->sync(7); // player
        User::findOrFail(12)->roles()->sync(7); // player
    }
}