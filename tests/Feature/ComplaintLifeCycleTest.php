<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Authority;
use App\Models\Department;
use App\Models\Complain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ComplaintLifeCycleTest extends TestCase
{
    use RefreshDatabase;

    protected $role;
    protected $authority;
    protected $department;
    protected $citizen;

    protected function setUp(): void
    {
        parent::setUp();

        // استخدام firstOrCreate بدلاً من create لمنع خطأ التكرار
        $this->role = Role::firstOrCreate(
            ['name' => 'citizen'],
            ['level' => 4]
        );

        $this->authority = Authority::firstOrCreate(
            ['name' => 'General Authority']
        );

        $this->department = Department::firstOrCreate(
            ['name' => 'IT Department', 'authority_id' => $this->authority->id]
        );

        $this->citizen = User::factory()->create([
            'role_id' => $this->role->id,
            'authority_id' => $this->authority->id,
            'is_verified' => true
        ]);
    }

    #[Test]
    public function verified_citizen_can_submit_complaint_with_attachment()
    {
        Storage::fake('public');
        $attachment = UploadedFile::fake()->image('evidence.jpg');

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->postJson('/api/complaints', [
                'title' => 'شكوى تجريبية',
                'description' => 'وصف تفصيلي للشكوى',
                'authority_id' => $this->authority->id,
                'department_id' => $this->department->id,
                'full_name' => 'Test Citizen Name',
                'attachment' => $attachment
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('complains', ['title' => 'شكوى تجريبية']);
    }

    #[Test]
    public function complaint_cannot_be_submitted_by_unverified_user()
    {
        $unverifiedUser = User::factory()->create([
            'role_id' => $this->role->id,
            'authority_id' => $this->authority->id,
            'is_verified' => false
        ]);

        $response = $this->actingAs($unverifiedUser, 'sanctum')
            ->postJson('/api/complaints', [
                'title' => 'شكوى مستخدم غير مفعل',
                'description' => 'وصف',
                'authority_id' => $this->authority->id,
                'department_id' => $this->department->id,
                'full_name' => 'Unverified User',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function complaint_status_transitions_correctly()
    {
        // تأكدي أن 'status' هنا يطابق أحد القيم المسموحة في قاعدة بياناتك (مثلاً new أو Pending)
        $complaint = Complain::create([
            'complain_number' => 'CMP-' . uniqid(),
            'full_name' => 'Test Name',
            'title' => 'شكوى للمتابعة',
            'description' => 'وصف الشكوى',
            'user_id' => $this->citizen->id,
            'authority_id' => $this->authority->id,
            'department_id' => $this->department->id,
            'status' => 'Pending', 
            'assigned_level' => 3
        ]);

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->postJson("/api/complaints/{$complaint->id}/status", [
                'status' => 'In Progress'
            ]);

        $response->assertStatus(200);
        $this->assertEquals('In Progress', $complaint->fresh()->status);
    }
}