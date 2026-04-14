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
            ['level' => 1, 'type' => 'citizen'],
            ['level' => 2, 'type' => 'employee'],
            ['level' => 3, 'type' => 'department_admin'],
            ['level' => 4, 'type' => 'super_admin'],
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['type' => $roleData['type']],
                $roleData
            );

            $this->assignPermissionsByRole($role);
        }
    }

    private function assignPermissionsByRole(Role $role): void
    {
        $role->permissions()->detach();

        match ($role->type) {
            'citizen' => $role->permissions()->attach(
                Permission::whereIn('name', ['view_complaints', 'create_complaint', 'chat_complaint', 'rate_authority'])->pluck('id')
            ),
            'employee' => $role->permissions()->attach(
                Permission::whereIn('name', ['view_complaints', 'update_complaint', 'chat_complaint'])->pluck('id')
            ),
            'department_admin' => $role->permissions()->attach(
                Permission::whereIn('name', ['view_complaints', 'update_complaint', 'assign_complaint', 'resolve_complaint', 'chat_complaint', 'manage_departments'])->pluck('id')
            ),
            'super_admin' => $role->permissions()->sync(Permission::pluck('id')),
            default => null,
        };
    }
}
