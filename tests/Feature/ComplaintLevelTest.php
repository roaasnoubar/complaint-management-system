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
        // 1. إنشاء الصلاحيات (الموظف مستواه 3)
        $authority = Authority::create(['name' => 'General Authority']);
        $role = Role::create(['name' => 'employee', 'level' => 3]);
        $dept = Department::create(['name' => 'IT', 'authority_id' => $authority->id]);

        $employee = User::factory()->create([
            'role_id' => $role->id,
            'authority_id' => $authority->id,
            'department_id' => $dept->id
        ]);

        // 2. إنشاء شكوى ذات مستوى أعلى (مستوى 5 مثلاً)
        // شرط الـ Controller: if (intval($user->role->level) < intval($complain->level)) { return 403; }
        // 3 < 5 هو true، إذن يجب أن يرجع 403
        $complaint = Complain::create([
            'complain_number' => 'CMP-' . uniqid(),
            'full_name' => 'Test Citizen Name',
            'user_id' => $employee->id,
            'title' => 'Emergency Issue',
            'description' => 'Description of the complaint',
            'level' => 5, // تأكدي أن هذا الرقم أكبر من 3
            'status' => 'Pending',
            'department_id' => $dept->id,
            'authority_id' => $authority->id,
            'assigned_level' => 3
        ]);

        // 3. محاولة التعديل
        $response = $this->actingAs($employee, 'sanctum')
                         ->postJson("/api/complaints/{$complaint->id}/status", ['status' => 'In Progress']);

        // 4. تأكيد الفشل (403)
        $response->assertStatus(403);
    }
}