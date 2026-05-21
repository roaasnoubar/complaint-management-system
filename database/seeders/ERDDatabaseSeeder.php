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
            PermissionSeeder::class, // 1. إنشاء الصلاحيات
            RoleSeeder::class,       // 2. إنشاء الأدوار الثابتة (من 1 إلى 5)
            AuthoritySeeder::class,  // 3. إنشاء الجهات وأقسامها (جامعة الشام، الوزارات...)
            UserSeeder::class,       // 4. إنشاء الحسابات الثابتة والموظفين بناءً على الأقسام أعلاه
        ]);
    }
}
