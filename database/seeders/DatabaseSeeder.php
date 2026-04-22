<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. تشغيل Seeder البيانات الأساسية (تأكدي أن الملف ERDDatabaseSeeder يحتوي على جامعة الشام والأدوار)
        $this->call(ERDDatabaseSeeder::class);

        // 2. جلب الأدوار
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $userRole = Role::where('name', 'user')->first();

        // 3. إنشاء حساب الأدمن
        User::updateOrCreate(['username' => 'admin_user'], [
            'name'        => 'مدير النظام',
            'email'       => 'admin@complaint.com',
            'phone'       => '0912345678',
            'password'    => Hash::make('password123'),
            'role_id'     => $adminRole?->id,
            'is_verified' => true, // مفعل تلقائياً
            'score'       => 100,
        ]);

        // 4. إنشاء حساب مستخدم (رؤى سنوبر)
        User::updateOrCreate(['username' => 'roaa_snoubar'], [
            'name'         => 'رؤى سنوبر',
            'email'        => 'roaa@example.com',
            'phone'        => '0987654321',
            'password'     => Hash::make('password123'),
            'role_id'      => $userRole?->id,
            'authority_id' => 1, // ربطها بجامعة الشام مباشرة
            'is_verified'  => true,
            'score'        => 0,
        ]);

        // 5. إنشاء حساب موظف (جامعة الشام)
        User::updateOrCreate(['username' => 'employee_shsham'], [
            'name'         => 'موظف جامعة الشام',
            'email'        => 'employee@shsham.edu.sy',
            'phone'        => '0933445566',
            'password'     => Hash::make('password123'),
            'role_id'      => $employeeRole?->id,
            'authority_id' => 1, // ربط الموظف بالجامعة
            'is_verified'  => true,
            'score'        => 50,
        ]);
    }
}