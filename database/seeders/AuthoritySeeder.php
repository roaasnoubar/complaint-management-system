<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AuthoritySeeder extends Seeder
{
    
    public function run(): void
    {
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
            $authority = Authority::updateOrCreate(
                ['name' => $authName],
                ['description' => 'جهة رسمية تابعة لنظام إدارة الشكاوى']
            );

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