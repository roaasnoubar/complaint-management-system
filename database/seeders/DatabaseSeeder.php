<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Authority;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. تشغيل Seeder البيانات الأساسية
        $this->call(ERDDatabaseSeeder::class);

        // 2. جلب الأدوار والجهات
        $adminRole    = Role::where('name', 'admin')->first();
        $managerRole  = Role::where('name', 'manager')->first(); 
        $employeeRole = Role::where('name', 'employee')->first();
        $userRole     = Role::where('name', 'user')->first();
        
        $shsham = Authority::where('name', 'جامعة الشام الخاصة')->first();

        // 3. إنشاء حساب الأدمن العام (Super Admin)
        User::updateOrCreate(['username' => 'admin_user'], [
            'name'        => 'مدير النظام',
            'email'       => 'admin@complaint.com',
            'phone'       => '0912345678',
            'password'    => Hash::make('password123'),
            'role_id'     => $adminRole?->id,
            'is_verified' => true,
            'score'       => 100,
        ]);

        // 4. إنشاء حساب مدير جهة جامعة الشام (الطلب الأول - ثابت)
        User::updateOrCreate(['username' => 'shsham_manager'], [
            'name'         => 'مدير جامعة الشام الخاصة',
            'email'        => 'manager@shsham.edu.sy',
            'phone'        => '0911223344',
            'password'     => Hash::make('shsham2026'), 
            'role_id'      => $managerRole?->id,
            'authority_id' => $shsham?->id,
            'is_verified'  => true,
            'score'        => 100,
        ]);

        // 5. إنشاء حسابات مديري الأقسام الثابتة لجامعة الشام (الطلب الثاني)
        $departments = [
            'دائرة الامتحانات'      => 'exams',
            'قسم النقل'            => 'transport',
            'قسم شؤون الطلبة'      => 'students',
            'قسم الشؤون الأكاديمية' => 'academic',
            'قسم المالية'           => 'finance',
        ];

        if ($shsham) {
            foreach ($departments as $deptName => $slug) {
                // جلب القسم من الداتابيز لربطه بالحساب
                $dept = Department::where('name', $deptName)
                    ->where('authority_id', $shsham->id)
                    ->first();

                if ($dept) {
                    User::updateOrCreate(['username' => "manager_{$slug}"], [
                        'name'          => "مدير " . $deptName,
                        'email'         => "{$slug}_mgr@shsham.edu.sy",
                        'phone'         => '09' . rand(10000000, 99999999),
                        'password'      => Hash::make('manager123'), // باسوورد ثابت لمديري الأقسام
                        'role_id'       => $managerRole?->id,
                        'authority_id'  => $shsham->id,
                        'department_id' => $dept->id,
                        'is_verified'   => true,
                        'score'         => 100,
                    ]);
                }
            }
        }

        // 6. إنشاء حساب مستخدم (رؤى سنوبر)
        User::updateOrCreate(['username' => 'roaa_snoubar'], [
            'name'         => 'رؤى سنوبر',
            'email'        => 'roaa@example.com',
            'phone'        => '0987654321',
            'password'     => Hash::make('password123'),
            'role_id'      => $userRole?->id,
            'authority_id' => $shsham?->id, 
            'is_verified'  => true,
            'score'        => 0,
        ]);

        // 7. إنشاء حساب موظف (جامعة الشام)
        User::updateOrCreate(['username' => 'employee_shsham'], [
            'name'         => 'موظف جامعة الشام',
            'email'        => 'employee@shsham.edu.sy',
            'phone'        => '0933445566',
            'password'     => Hash::make('password123'),
            'role_id'      => $employeeRole?->id,
            'authority_id' => $shsham?->id, 
            'is_verified'  => true,
            'score'        => 50,
        ]);
        // 8. إنشاء حسابات الموظفين الثابتة لجامعة الشام (الطلب الثالث)
        $employeeData = [
            'دائرة الامتحانات'      => 'exams',
            'قسم النقل'            => 'transport',
            'قسم شؤون الطلبة'      => 'students',
            'قسم الشؤون الأكاديمية' => 'academic',
            'قسم المالية'           => 'finance',
        ];

        if ($shsham) {
            foreach ($employeeData as $deptName => $slug) {
                // جلب القسم لربط الموظف به
                $dept = Department::where('name', $deptName)
                    ->where('authority_id', $shsham->id)
                    ->first();

                if ($dept) {
                    User::updateOrCreate(['username' => "emp_{$slug}"], [
                        'name'          => "موظف " . $deptName,
                        'email'         => "{$slug}_emp@shsham.edu.sy",
                        'phone'         => '09' . rand(10000000, 99999999),
                        'password'      => Hash::make('emp123'), // باسوورد ثابت للموظفين
                        'role_id'       => $employeeRole?->id, // دور موظف (Level 3)
                        'authority_id'  => $shsham->id,
                        'department_id' => $dept->id,
                        'is_verified'   => true,
                        'score'         => 50, // سكور الموظف المبدئي
                    ]);
                }
            }
        }
    }
}