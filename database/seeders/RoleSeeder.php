<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['level' => 1, 'name' => 'user'],
            ['level' => 2, 'name' => 'employee'],
            ['level' => 3, 'name' => 'admin'],
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            $this->assignPermissionsByRole($role);
        }
    }

    private function assignPermissionsByRole(Role $role): void
    {
        $role->permissions()->detach();

        match ($role->name) {
            'user' => $role->permissions()->attach(
                Permission::whereIn('name', ['view_complaints', 'create_complaint', 'chat_complaint', 'rate_authority'])->pluck('id')
            ),
            'employee' => $role->permissions()->attach(
                Permission::whereIn('name', ['view_complaints', 'update_complaint', 'chat_complaint'])->pluck('id')
            ),
            'admin' => $role->permissions()->sync(Permission::pluck('id')),
            default => null,
        };
    }
}
