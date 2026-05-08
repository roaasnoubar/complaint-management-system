<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Complain;
use App\Models\Authority;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EscalationTest extends TestCase
{
    use RefreshDatabase;

    protected $lowLevelEmp;
    protected $highLevelEmp;
    protected $complaint;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'level' => 0]);
        $managerRole = Role::create(['name' => 'manager', 'level' => 3]);
        $empRole = Role::create(['name' => 'employee', 'level' => 1]);

        $auth = Authority::create(['name' => 'وزارة الصحة']);
        $dept = Department::create(['name' => 'قسم الشكاوي', 'authority_id' => $auth->id]);

        $this->lowLevelEmp = User::factory()->create(['role_id' => $empRole->id, 'authority_id' => $auth->id]);
        $this->highLevelEmp = User::factory()->create(['role_id' => $managerRole->id, 'authority_id' => $auth->id]);

        $this->complaint = Complain::create([
            'complain_number' => 'ESC-101',
            'title' => 'شكوى طبية',
            'user_id' => User::factory()->create()->id,
            'authority_id' => $auth->id,
            'department_id' => $dept->id,
            'assigned_level' => 1,
            'status' => 'Pending',
            'full_name' => 'مواطن',
            'description' => 'وصف'
        ]);
    }

    #[Test]
    public function low_level_employee_cannot_escalate_complaint()
    {
        // محاولة تصعيد من ليفل 1 إلى ليفل 3
        $response = $this->actingAs($this->lowLevelEmp, 'sanctum')
            ->patchJson("/api/complaints/{$this->complaint->id}/escalate", [
                'target_level' => 3
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function manager_can_escalate_complaint()
    {
        // تصعيد ناجح من قبل المدير
        $response = $this->actingAs($this->highLevelEmp, 'sanctum')
            ->patchJson("/api/complaints/{$this->complaint->id}/escalate", [
                'target_level' => 3
            ]);

        $response->assertStatus(200);
        $this->assertEquals(3, $this->complaint->fresh()->assigned_level);
    }
}