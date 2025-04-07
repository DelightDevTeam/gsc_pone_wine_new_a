<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'title' => 'senior_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'game_type_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'admin_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_create',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_edit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_delete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_create',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_edit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_delete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'agent_change_password_access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'transfer_log',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'make_transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'withdraw',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'deposit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'contact',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_create',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_edit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_delete',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'system_wallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'sub_acc',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Permission::insert($permissions);
    }
}
