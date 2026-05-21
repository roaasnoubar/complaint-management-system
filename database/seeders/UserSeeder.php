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
        // تم تعديل الـ IDs هنا لتتطابق تماماً مع ترتيب الأقسام في الـ AuthoritySeeder الخاص بكِ
        // جامعة الشام الخاصة هي أول جهة، وبالتالي authority_id = 1
        // ترتيب الأقسام داخلها: الامتحانات (1)، النقل (2)، شؤون الطلبة (3)، الأكاديمية (4)، المالية (5)

        // --------------------------------------------------------------------------------
        // 1. السوبر أدمن (Role ID = 1)
        // --------------------------------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'admin@complaint.com'],
            [
                'name' => 'مدير النظام (Super Admin)',
                'phone_number' => '+963900000000',
                'role_id' => 1,
                'password' => Hash::make('password123'),
            ]
        );

        // --------------------------------------------------------------------------------
        // 2. مدير الجهة العليا - رئيس الجامعة (Role ID = 2)
        // --------------------------------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'manager@shsham.edu.sy'],
            [
                'name' => 'رئيس جامعة الشام (مدير الجهة)',
                'phone_number' => '+963911111111',
                'role_id' => 2,
                'authority_id' => 1, // جامعة الشام (الجهة رقم 1)
                'password' => Hash::make('shsham2026'),
            ]
        );

        // --------------------------------------------------------------------------------
        // 3. مدراء الأقسام (Role ID = 3)
        // --------------------------------------------------------------------------------
        
        // مدير قسم الامتحانات (department_id = 1)
        User::updateOrCreate(
            ['email' => 'exams_mgr@shsham.edu.sy'],
            [
                'name' => 'مدير دائرة الامتحانات',
                'phone_number' => '+963921111111',
                'role_id' => 3,
                'department_id' => 1, 
                'password' => Hash::make('manager123'),
            ]
        );

        // مدير قسم النقل والمواصلات (department_id = 2)
        User::updateOrCreate(
            ['email' => 'transport_mgr@shsham.edu.sy'],
            [
                'name' => 'مدير قسم النقل',
                'phone_number' => '+963922222222',
                'role_id' => 3,
                'department_id' => 2,
                'password' => Hash::make('manager123'),
            ]
        );

        // مدير قسم شؤون الطلاب (department_id = 3)
        User::updateOrCreate(
            ['email' => 'students_mgr@shsham.edu.sy'],
            [
                'name' => 'مدير شؤون الطلبة',
                'phone_number' => '+963923333333',
                'role_id' => 3,
                'department_id' => 3,
                'password' => Hash::make('manager123'),
            ]
        );

        // مدير الشؤون الأكاديمية (department_id = 4)
        User::updateOrCreate(
            ['email' => 'academic_mgr@shsham.edu.sy'],
            [
                'name' => 'مدير الشؤون الأكاديمية',
                'phone_number' => '+963924444444',
                'role_id' => 3,
                'department_id' => 4,
                'password' => Hash::make('manager123'),
            ]
        );

        // مدير قسم المالية (department_id = 5)
        User::updateOrCreate(
            ['email' => 'finance_mgr@shsham.edu.sy'],
            [
                'name' => 'مدير قسم المالية',
                'phone_number' => '+963925555555',
                'role_id' => 3,
                'department_id' => 5,
                'password' => Hash::make('manager123'),
            ]
        );

        // --------------------------------------------------------------------------------
        // 4. الموظفون التنفيذيون في الأقسام (Role ID = 4)
        // --------------------------------------------------------------------------------
        
        // موظف الامتحانات
        User::updateOrCreate(
            ['email' => 'exams_emp@shsham.edu.sy'],
            [
                'name' => 'موظف دائرة الامتحانات',
                'phone_number' => '+963941111111',
                'role_id' => 4,
                'department_id' => 1,
                'password' => Hash::make('emp123'),
            ]
        );

        // موظف النقل
        User::updateOrCreate(
            ['email' => 'transport_emp@shsham.edu.sy'],
            [
                'name' => 'موظف قسم النقل',
                'phone_number' => '+963942222222',
                'role_id' => 4,
                'department_id' => 2,
                'password' => Hash::make('emp123'),
            ]
        );

        // موظف شؤون الطلاب
        User::updateOrCreate(
            ['email' => 'students_emp@shsham.edu.sy'],
            [
                'name' => 'موظف شؤون الطلبة',
                'phone_number' => '+963943333333',
                'role_id' => 4,
                'department_id' => 3,
                'password' => Hash::make('emp123'),
            ]
        );

        // موظف الشؤون الأكاديمية
        User::updateOrCreate(
            ['email' => 'academic_emp@shsham.edu.sy'],
            [
                'name' => 'موظف الشؤون الأكاديمية',
                'phone_number' => '+963944444444',
                'role_id' => 4,
                'department_id' => 4,
                'password' => Hash::make('emp123'),
            ]
        );

        // موظف المالية
        User::updateOrCreate(
            ['email' => 'finance_emp@shsham.edu.sy'],
            [
                'name' => 'موظف قسم المالية',
                'phone_number' => '+963945555555',
                'role_id' => 4,
                'department_id' => 5,
                'password' => Hash::make('emp123'),
            ]
        );

        // --------------------------------------------------------------------------------
        // 5. حساب طالب تجريبي (Role ID = 5)
        // --------------------------------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'student_test@shsham.edu.sy'],
            [
                'name' => 'الطالب التجريبي (رامي)',
                'phone_number' => '+963955555555',
                'role_id' => 5,
                'score' => 100,
                'false_complaints_count' => 0,
                'is_banned' => false,
                'password' => Hash::make('password123'),
            ]
        );
    }
}