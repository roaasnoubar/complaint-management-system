<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Authority;
use App\Models\Department;
use App\Models\Complain;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplaintLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_update_high_level_complaint()
    {
        // 1. إنشاء البيانات الأساسية
        $authority = Authority::create(['name' => 'General Authority']);
        $role = Role::create(['name' => 'employee', 'level' => 3]);
        $dept = Department::create(['name' => 'IT', 'authority_id' => $authority->id]);

        // 2. إنشاء الموظف
        $employee = User::factory()->create([
            'role_id' => $role->id,
            'authority_id' => $authority->id,
            'department_id' => $dept->id
        ]);

        // 3. إنشاء الشكوى مع إضافة الحقول الإجبارية (full_name و user_id)
        $complaint = Complain::create([
            'complain_number' => 'CMP-' . uniqid(),
            'full_name' => 'Test Citizen Name', // الحقل الذي كان يسبب الخطأ
            'user_id' => $employee->id,         // ربط الشكوى بمستخدم
            'title' => 'Emergency Issue',
            'description' => 'Description of the complaint',
            'level' => 1,                       // مستوى أعلى من الموظف
            'status' => 'Pending',
            'department_id' => $dept->id,
            'authority_id' => $authority->id,
            'assigned_level' => 3
        ]);

        // 4. محاولة التعديل وتوقع رفض الوصول (403)
        $response = $this->actingAs($employee)
                         ->postJson("/api/complaints/{$complaint->id}/status", ['status' => 'In Progress']);

        $response->assertStatus(403);
    }
}