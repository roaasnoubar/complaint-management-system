<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ERDDatabaseSeeder extends Seeder
{
    /**
     * Seed the ERD-based complaint management database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AuthoritySeeder::class,
            DepartmentSeeder::class,
        ]);
    }
}
