<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'view_complaints', 'description' => 'View complaints'],
            ['name' => 'create_complaint', 'description' => 'Create new complaints'],
            ['name' => 'update_complaint', 'description' => 'Update complaints'],
            ['name' => 'delete_complaint', 'description' => 'Delete complaints'],
            ['name' => 'assign_complaint', 'description' => 'Assign complaints to departments'],
            ['name' => 'resolve_complaint', 'description' => 'Resolve complaints'],
            ['name' => 'manage_users', 'description' => 'Manage users and roles'],
            ['name' => 'manage_departments', 'description' => 'Manage departments'],
            ['name' => 'manage_authorities', 'description' => 'Manage authorities'],
            ['name' => 'chat_complaint', 'description' => 'Participate in complaint chats'],
            ['name' => 'rate_authority', 'description' => 'Rate authority response'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
