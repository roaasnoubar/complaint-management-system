<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@complaint.com',
                'username' => 'super_admin',
                'name' => 'مدير النظام (Super Admin)',
                'phone' => '+963900000000',
                'role_id' => 1,
                'authority_id' => null,
                'department_id' => null,
                'password' => Hash::make('password123'),
            ],
            [
                'email' => 'manager@shsham.edu.sy',
                'username' => 'shsham_rector',
                'name' => 'رئيس جامعة الشام (مدير الجهة)',
                'phone' => '+963911111111',
                'role_id' => 2,
                'authority_id' => 1,
                'department_id' => null,
                'password' => Hash::make('shsham2026'),
            ],

            [
                'email' => 'exams_mgr@shsham.edu.sy',
                'username' => 'exams_manager_shsham', 
                'name' => 'مدير دائرة الامتحانات',
                'phone' => '+963921111111',
                'role_id' => 3,
                'authority_id' => null,
                'department_id' => 1,
                'password' => Hash::make('manager123'),
            ],
            [
                'email' => 'transport_mgr@shsham.edu.sy',
                'username' => 'transport_manager_shsham', 
                'name' => 'مدير قسم النقل',
                'phone' => '+963922222222',
                'role_id' => 3,
                'authority_id' => null,
                'department_id' => 2,
                'password' => Hash::make('manager123'),
            ],
            [
                'email' => 'students_mgr@shsham.edu.sy',
                'username' => 'students_manager_shsham',
                'name' => 'مدير شؤون الطلبة',
                'phone' => '+963923333333',
                'role_id' => 3,
                'authority_id' => null,
                'department_id' => 3,
                'password' => Hash::make('manager123'),
            ],
            [
                'email' => 'academic_mgr@shsham.edu.sy',
                'username' => 'academic_manager_shsham',
                'name' => 'مدير الشؤون الأكاديمية',
                'phone' => '+963924444444',
                'role_id' => 3,
                'authority_id' => null,
                'department_id' => 4,
                'password' => Hash::make('manager123'),
            ],
            [
                'email' => 'finance_mgr@shsham.edu.sy',
                'username' => 'finance_manager_shsham',
                'name' => 'مدير قسم المالية',
                'phone' => '+963925555555',
                'role_id' => 3,
                'authority_id' => null,
                'department_id' => 5,
                'password' => Hash::make('manager123'),
            ],

            [
                'email' => 'exams_emp@shsham.edu.sy',
                'username' => 'exams_employee_shsham',
                'name' => 'موظف دائرة الامتحانات',
                'phone' => '+963941111111',
                'role_id' => 4,
                'authority_id' => null,
                'department_id' => 1,
                'password' => Hash::make('emp123'),
            ],
            [
                'email' => 'transport_emp@shsham.edu.sy',
                'username' => 'transport_employee_shsham',
                'name' => 'موظف قسم النقل',
                'phone' => '+963942222222',
                'role_id' => 4,
                'authority_id' => null,
                'department_id' => 2,
                'password' => Hash::make('emp123'),
            ],
            [
                'email' => 'students_emp@shsham.edu.sy',
                'username' => 'students_employee_shsham',
                'name' => 'موظف شؤون الطلبة',
                'phone' => '+963943333333',
                'role_id' => 4,
                'authority_id' => null,
                'department_id' => 3,
                'password' => Hash::make('emp123'),
            ],
            [
                'email' => 'academic_emp@shsham.edu.sy',
                'username' => 'academic_employee_shsham',
                'name' => 'موظف الشؤون الأكاديمية',
                'phone' => '+963944444444',
                'role_id' => 4,
                'authority_id' => null,
                'department_id' => 4,
                'password' => Hash::make('emp123'),
            ],
            [
                'email' => 'finance_emp@shsham.edu.sy',
                'username' => 'finance_employee_shsham',
                'name' => 'موظف قسم المالية',
                'phone' => '+963945555555',
                'role_id' => 4,
                'authority_id' => null,
                'department_id' => 5,
                'password' => Hash::make('emp123'),
            ],

            [
                'email' => 'student_test@shsham.edu.sy',
                'username' => 'rami_student_shsham',
                'name' => 'الطالب التجريبي (رامي)',
                'phone' => '+963955555555',
                'role_id' => 5,
                'authority_id' => null,
                'department_id' => null,
                'score' => 100,
                'false_complaints_count' => 0,
                'is_active' => true,
                'is_banned' => false,
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
        }
    }
}