<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Authority;
use App\Models\Department;
use App\Models\Complain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SecurityPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected $citizenA;
    protected $citizenB;
    protected $electricityEmp;
    protected $elecAuth;
    protected $waterAuth;
    protected $elecDept;
    protected $waterDept;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. إنشاء الأدوار
        $citizenRole = Role::firstOrCreate(['name' => 'citizen'], ['level' => 4]);
        $employeeRole = Role::firstOrCreate(['name' => 'employee'], ['level' => 1]);

        // 2. إنشاء الجهات والأقسام
        $this->elecAuth = Authority::create(['name' => 'وزارة الكهرباء']);
        $this->waterAuth = Authority::create(['name' => 'وزارة المياه']);
        
        $this->elecDept = Department::create(['name' => 'الصيانة', 'authority_id' => $this->elecAuth->id]);
        $this->waterDept = Department::create(['name' => 'الفوترة', 'authority_id' => $this->waterAuth->id]);

        // 3. إنشاء المواطنين مع ربطهم بالأدوار والجهات الصحيحة
        $this->citizenA = User::factory()->create([
            'role_id' => $citizenRole->id, 
            'authority_id' => $this->elecAuth->id,
            'is_verified' => true
        ]);
        
        $this->citizenB = User::factory()->create([
            'role_id' => $citizenRole->id, 
            'authority_id' => $this->elecAuth->id,
            'is_verified' => true
        ]);

        // 4. إنشاء موظف الكهرباء
        $this->electricityEmp = User::factory()->create([
            'role_id' => $employeeRole->id,
            'authority_id' => $this->elecAuth->id,
            'is_verified' => true
        ]);
    }

    #[Test]
    public function citizen_cannot_view_another_citizens_complaint()
    {
        $complaintB = Complain::create([
            'complain_number' => 'CMP-B-123',
            'title' => 'شكوى خاصة بمواطن ب',
            'user_id' => $this->citizenB->id,
            'authority_id' => $this->waterAuth->id,
            'department_id' => $this->waterDept->id, // أضفنا القسم هنا
            'status' => 'Pending',
            'full_name' => $this->citizenB->name,
            'description' => 'تفاصيل سرية',
            'assigned_level' => 3
        ]);

        $response = $this->actingAs($this->citizenA, 'sanctum')
            ->getJson("/api/complaints/{$complaintB->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function employee_cannot_view_complaints_of_another_authority()
    {
        $waterComplaint = Complain::create([
            'complain_number' => 'WTR-999',
            'title' => 'انقطاع مياه',
            'user_id' => $this->citizenB->id,
            'authority_id' => $this->waterAuth->id,
            'department_id' => $this->waterDept->id, // أضفنا القسم هنا
            'status' => 'Pending',
            'full_name' => 'مواطن متضرر',
            'description' => 'المياه مقطوعة',
            'assigned_level' => 1
        ]);

        $response = $this->actingAs($this->electricityEmp, 'sanctum')
            ->getJson("/api/complaints/{$waterComplaint->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function lower_level_employee_cannot_access_escalated_complaint()
    {
        $escalatedComplaint = Complain::create([
            'complain_number' => 'ESC-001',
            'title' => 'شكوى حساسة ومصعدة',
            'user_id' => $this->citizenB->id,
            'authority_id' => $this->elecAuth->id,
            'department_id' => $this->elecDept->id, // أضفنا القسم هنا
            'assigned_level' => 3, 
            'status' => 'In Progress',
            'full_name' => 'مواطن',
            'description' => 'وصف'
        ]);

        $response = $this->actingAs($this->electricityEmp, 'sanctum')
            ->getJson("/api/complaints/{$escalatedComplaint->id}");

        $response->assertStatus(403);
    }
}