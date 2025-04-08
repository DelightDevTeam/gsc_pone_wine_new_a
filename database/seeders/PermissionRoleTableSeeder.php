<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Senior Permissions
        $senior_owner_permissions = Permission::whereIn('title', [
            'senior_owner_access',
            'owner_index',
            'owner_create',
            'owner_edit',
            'owner_delete',
            'transfer_log',
            'make_transfer',
            'game_type_access',
        ]);
        Role::findOrFail(1)->permissions()->sync($senior_owner_permissions->pluck('id'));

        //Owner Permissions
        $owner_permissions = Permission::whereIn('title', [
            'owner_access',
            'senior_index',
            'senior_create',
            'senior_edit',
            'senior_delete',
            'transfer_log',
            'make_transfer',
        ]);
        Role::findOrFail(2)->permissions()->sync($owner_permissions->pluck('id'));

        //Senior Permissions
        $senior_permissions = Permission::whereIn('title', [
            'senior_access',
            'master_index',
            'master_create',
            'master_edit',
            'master_delete',
            'transfer_log',
            'make_transfer',
        ]);
        Role::findOrFail(3)->permissions()->sync($senior_permissions->pluck('id'));

        // master permissions
        $master_permissions = Permission::whereIn('title', [
            'master_access',
            'agent_index',
            'agent_create',
            'agent_edit',
            'agent_delete',
            'agent_change_password_access',
            'transfer_log',
            'make_transfer',
        ]);
        Role::findOrFail(4)->permissions()->sync($master_permissions->pluck('id'));

        $agent_permissions = Permission::whereIn('title', [
            'agent_access',
            'player_index',
            'player_create',
            'player_edit',
            'player_delete',
            'transfer_log',
            'make_transfer',
            'withdraw',
            'deposit',
            'bank',
            'site_logo',
            'contact',
        ])->pluck('id');

        Role::findOrFail(5)->permissions()->sync($agent_permissions);

        $systemWallet = Permission::where('title', 'system_wallet')->first();
        Role::findOrFail(6)->permissions()->sync($systemWallet);
    }
}
