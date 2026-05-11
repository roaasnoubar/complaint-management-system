<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AuthoritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تعريف البيانات: جامعة الشام تحتوي على كل الأقسام، والوزارات تتبع اختصاصاتها
        $data = [
            'جامعة الشام الخاصة' => [
                'دائرة الامتحانات',
                'قسم النقل',
                'قسم شؤون الطلبة',
                'قسم الشؤون الأكاديمية',
                'قسم المالية'
            ],
            'وزارة التعليم العالي' => [
                'قسم شؤون الطلبة',
                'قسم الشؤون الأكاديمية',
                'دائرة الامتحانات'
            ],
            'وزارة النقل' => [
                'قسم النقل البري',
                'قسم المالية',
                'قسم الرخص'
            ],
            'وزارة التربية' => [
                'قسم المناهج',
                'قسم الامتحانات الثانوى',
                'قسم الشؤون الإدارية'
            ],
        ];

        foreach ($data as $authName => $departments) {
            // 1. إنشاء أو تحديث الجهة (Authority)
            $authority = Authority::updateOrCreate(
                ['name' => $authName],
                ['description' => 'جهة رسمية تابعة لنظام إدارة الشكاوى']
            );

            // 2. إضافة الأقسام (Departments) التابعة لهذه الجهة
            foreach ($departments as $deptName) {
                Department::updateOrCreate(
                    [
                        'name' => $deptName,
                        'authority_id' => $authority->id
                    ]
                );
            }
        }
    }
}