<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
{
    $roles = [
        ['id' => 1, 'name' => 'admin',        'level' => 0],
        ['id' => 2, 'name' => 'manager',      'level' => 1],
        ['id' => 3, 'name' => 'dept_manager', 'level' => 2],
        ['id' => 4, 'name' => 'employee',     'level' => 3],
        ['id' => 5, 'name' => 'user',         'level' => 4],
    ];

    foreach ($roles as $roleData) {
        // قمنا بتخزين الكائن الناتج عن العملية في متغير اسمه $roleModel
        $roleModel = \App\Models\Role::updateOrCreate(
            ['id' => $roleData['id']], 
            [
                'name'  => $roleData['name'],
                'level' => $roleData['level'],
            ]
        );

        // الآن نمرر الكائن (Model) وليس المصفوفة
        $this->assignPermissionsByRole($roleModel);
    }
}
    private function assignPermissionsByRole(Role $role): void
    {
        $role->permissions()->detach();

        match ($role->name) {
            'user' => $role->permissions()->attach(
                Permission::whereIn('name', [
                    'view_complaints',
                    'create_complaint',
                    'chat_complaint',
                    'rate_authority',
                ])->pluck('id')
            ),

            'employee' => $role->permissions()->attach(
                Permission::whereIn('name', [
                    'view_complaints',
                    'update_complaint',
                    'chat_complaint',
                ])->pluck('id')
            ),

            'admin' => $role->permissions()->sync(Permission::pluck('id')),

            default => null,
        };
    }
}