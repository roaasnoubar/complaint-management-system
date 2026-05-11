<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_and_receive_an_otp()
    {
        // منع إرسال إيميلات حقيقية أثناء التيست
        Mail::fake();

        $userData = [
            'name' => 'Ahmad Student',
            'phone' => '0599000111',
            'email' => 'ahmad@aspu.edu.sy',
            'birthdate' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        // 1. التأكد من نجاح الطلب
        $response->assertStatus(201);
        
        // 2. التأكد من إنشاء المستخدم وحقل الـ username التلقائي
        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@aspu.edu.sy',
            'is_verified' => false,
            'role_id' => 3 // دور المواطن/المستخدم العادي
        ]);

        // 3. التأكد من إرسال إيميل الـ OTP
        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function user_can_verify_email_with_correct_otp_and_receive_token()
    {
        // 1. إنشاء مستخدم غير مفعل يدوياً
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser_123',
            'phone' => '0599123456',
            'email' => 'test@example.com',
            'birthdate' => '1995-05-05',
            'password' => bcrypt('password123'),
            'verification_code' => '123456',
            'verification_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
            'role_id' => 3,
            'authority_id' => 1
        ]);

        // 2. إرسال كود التحقق الصحيح
        $response = $this->postJson('/api/verify-email', [
            'email' => 'test@example.com',
            'code'  => '123456',
        ]);

        // 3. التأكد من تفعيل الحساب والحصول على توكن
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'data' => ['token', 'user']
                 ]);

        $this->assertTrue($user->fresh()->is_verified);
    }

    /** @test */
    public function registration_fails_if_email_is_already_taken()
    {
        // إنشاء مستخدم موجود مسبقاً
        User::factory()->create(['email' => 'duplicate@example.com']);

        $userData = [
            'name' => 'New User',
            'phone' => '0599000222',
            'email' => 'duplicate@example.com',
            'birthdate' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        // يجب أن يفشل التحقق (Validation Error)
        $response->assertStatus(422);
    }
}