<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Authority;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication; // هذا هو السطر الذي كان يسبب الخطأ

    protected function setUp(): void
    {
        parent::setUp();

        // إنشاء البيانات الأساسية لحل مشكلة الـ Foreign Key
        $this->seedBasicData();
    }

    private function seedBasicData(): void
    {
        try {
            if (Schema::hasTable('roles')) {
                Role::firstOrCreate(['id' => 3], ['name' => 'Citizen']);
            }
            if (Schema::hasTable('authorities')) {
                Authority::firstOrCreate(['id' => 1], ['name' => 'General Authority']);
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كانت الجداول لم تُنشأ بعد
        }
    }
}