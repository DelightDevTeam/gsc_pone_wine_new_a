<?php

namespace Database\Seeders;

use App\Models\Admin\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [

                'title' => 'Senior Owner', // 1
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Owner', //2
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Super', //3
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Senior', //4
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Master', //5
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Agent', // 6
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'Player', // 7
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'title' => 'System Wallet', // 8
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        Role::insert($roles);
    }
}