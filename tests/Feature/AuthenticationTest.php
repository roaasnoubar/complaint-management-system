<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Authority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $role;
    protected $authority;

    protected function setUp(): void
    {
        parent::setUp();

        // استخدام firstOrCreate لتجنب خطأ Duplicate Entry
        $this->role = Role::firstOrCreate(
            ['name' => 'Citizen'],
            ['level' => 4]
        );

        $this->authority = Authority::firstOrCreate(
            ['name' => 'General Authority']
        );
    }

    #[Test]
    public function a_user_can_register_successfully()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0912345678',
            'birthdate' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function a_user_can_verify_their_account_with_otp()
    {
        $user = User::factory()->create([
            'email' => 'verify@test.com',
            'verification_code' => '111222',
            'is_verified' => false,
            'verification_expires_at' => now()->addMinutes(10),
            'role_id' => $this->role->id,
            'authority_id' => $this->authority->id,
        ]);

        $response = $this->postJson('/api/auth/verify-email', [
            'email' => 'verify@test.com',
            'code' => '111222'
        ]);

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->is_verified);
    }

    #[Test]
    public function user_cannot_login_if_not_verified()
    {
        $user = User::factory()->create([
            'username' => 'test_user_unique',
            'email' => 'notverified@test.com',
            'password' => bcrypt('password123'),
            'is_verified' => false,
            'role_id' => $this->role->id,
            'authority_id' => $this->authority->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'test_user_unique',
            'password' => 'password123'
        ]);

        // تأكدي أن الـ Controller يرجع 403 أو 401 عند عدم التفعيل
        // إذا فشل هنا، جربي تغيير assertStatus لـ 401 أو 422 حسب رد سيرفرك
        $response->assertStatus(403); 
    }
}