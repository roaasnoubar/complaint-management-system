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
        $this->call(ERDDatabaseSeeder::class);

        $adminRole    = Role::where('name', 'admin')->first();
        $managerRole  = Role::where('name', 'manager')->first(); 
        $employeeRole = Role::where('name', 'employee')->first();
        $userRole     = Role::where('name', 'user')->first();
        
        $shsham = Authority::where('name', 'جامعة الشام الخاصة')->first();

       
        User::updateOrCreate(['username' => 'admin_user'], [
            'name'        => 'مدير النظام',
            'email'       => 'admin@complaint.com',
            'phone'       => '0912345678',
            'password'    => Hash::make('password123'),
            'role_id'     => $adminRole?->id,
            'is_verified' => true,
            'score'       => 100,
        ]);

    
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
                        'password'      => Hash::make('manager123'), 
                        'role_id'       => $managerRole?->id,
                        'authority_id'  => $shsham->id,
                        'department_id' => $dept->id,
                        'is_verified'   => true,
                        'score'         => 100,
                    ]);
                }
            }
        }

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
        $employeeData = [
            'دائرة الامتحانات'      => 'exams',
            'قسم النقل'            => 'transport',
            'قسم شؤون الطلبة'      => 'students',
            'قسم الشؤون الأكاديمية' => 'academic',
            'قسم المالية'           => 'finance',
        ];

        if ($shsham) {
            foreach ($employeeData as $deptName => $slug) {
                $dept = Department::where('name', $deptName)
                    ->where('authority_id', $shsham->id)
                    ->first();

                if ($dept) {
                    User::updateOrCreate(['username' => "emp_{$slug}"], [
                        'name'          => "موظف " . $deptName,
                        'email'         => "{$slug}_emp@shsham.edu.sy",
                        'phone'         => '09' . rand(10000000, 99999999),
                        'password'      => Hash::make('emp123'), 
                        'role_id'       => $employeeRole?->id, 
                        'authority_id'  => $shsham->id,
                        'department_id' => $dept->id,
                        'is_verified'   => true,
                        'score'         => 50, 
                    ]);
                }
            }
        }
    }
}