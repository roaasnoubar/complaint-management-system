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
        ['id' => 1, 'name' => 'admin',    'level' => 3],
        ['id' => 2, 'name' => 'employee', 'level' => 2],
        ['id' => 3, 'name' => 'user',     'level' => 1],
    ];

    foreach ($roles as $roleData) {
        // قمنا بإضافة $role = هنا لتعريف المتغير
        $role = \App\Models\Role::updateOrCreate(
            ['id' => $roleData['id']], 
            $roleData
        );

        // الآن المتغير $role أصبح موجوداً ولن يظهر الخطأ
        $this->assignPermissionsByRole($role);
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