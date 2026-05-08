<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Authority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class UserAuthTest extends TestCase
{
    // هذه الخاصية ستقوم بتفريغ قاعدة البيانات وإعادة بنائها قبل كل اختبار لضمان بيئة نظيفة
    use RefreshDatabase;

    protected $role;
    protected $auth;

    protected function setUp(): void
    {
        parent::setUp();
        
        // نستخدم firstOrCreate لنتجنب خطأ التكرار (Duplicate Entry)
        $this->role = Role::firstOrCreate(
            ['name' => 'user'],
            ['level' => 4]
        );
        
        $this->auth = Authority::firstOrCreate(
            ['name' => 'General Authority']
        );
    }

    public function test_a_user_can_register_and_receive_an_otp()
    {
        Mail::fake();

        $userData = [
            'name' => 'Ahmad Student',
            'username' => 'ahmad_' . uniqid(),
            'phone' => '0599' . rand(1000, 9999),
            'email' => 'ahmad_' . uniqid() . '@aspu.edu.sy',
            'birthdate' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->role->id,
            'authority_id' => $this->auth->id
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        
        // للتأكد من حالة الاستجابة وطباعة الخطأ إن وجد
        if ($response->status() !== 201) {
            dump($response->json());
        }

        $response->assertStatus(201);
        Mail::assertSent(OtpMail::class);
    }

    public function test_user_can_verify_email_with_correct_otp_and_receive_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser_' . uniqid(),
            'phone' => '0599' . rand(1000, 9999),
            'email' => 'test_' . uniqid() . '@example.com',
            'birthdate' => '1995-05-05',
            'password' => bcrypt('password123'),
            'verification_code' => '123456',
            'verification_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
            'role_id' => $this->role->id,
            'authority_id' => $this->auth->id
        ]);

        $response = $this->postJson('/api/auth/verify-email', [
            'email' => $user->email,
            'code'  => '123456',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_registration_fails_if_email_is_already_taken()
    {
        $email = 'taken@example.com';

        // إنشاء مستخدم موجود مسبقاً بنفس الإيميل
        User::create([
            'name' => 'Existing User',
            'username' => 'existing_user',
            'email' => $email,
            'password' => bcrypt('password123'),
            'role_id' => $this->role->id,
            'authority_id' => $this->auth->id,
            'phone' => '0912345678'
        ]);

        $userData = [
            'name' => 'New User',
            'username' => 'new_user',
            'phone' => '0987654321',
            'email' => $email, // نفس الإيميل
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->role->id,
            'authority_id' => $this->auth->id
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        
        // نتوقع خطأ Validation (422) لأن الإيميل مأخوذ
        $response->assertStatus(422);
    }
}