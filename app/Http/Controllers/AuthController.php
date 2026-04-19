<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد مع كامل البيانات والسكور الابتدائي
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'email'     => 'required|email|unique:users,email',
            'birthdate' => 'required|date|before:today',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        $verificationCode = rand(100000, 999999);

        // الموديل سيقوم بتشفير الباسوورد تلقائياً بفضل دالة setPasswordAttribute
        $user = User::create([
            'name'                    => $validated['name'],
            'phone'                   => $validated['phone'],
            'email'                   => $validated['email'],
            'birthdate'               => $validated['birthdate'],
            'password'                => $validated['password'], 
            'verification_code'       => $verificationCode,
            'verification_expires_at' => now()->addMinutes(10),
            'is_verified'             => false,
            'role_id'                 => 3, // ID الطالب الافتراضي
            'authority_id'            => 1, // الهيئة الافتراضية
            'score'                   => 0, // بداية السكور للاختبار
            'is_active'               => true,
            'is_banned'               => false,
            'false_complaints_count'  => 0,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail((string)$verificationCode, $user->name));
        } catch (\Exception $e) {
            \Log::error("Mail Error: " . $e->getMessage());
        }

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for the verification code.',
            'data'    => [
                'user' => [
                    'id'                     => $user->id,
                    'name'                   => $user->name,
                    'email'                  => $user->email,
                    'phone'                  => $user->phone,
                    'birthdate'              => $user->birthdate ? $user->birthdate->format('Y-m-d') : null,
                    'role_id'                => $user->role_id,
                    'authority_id'           => $user->authority_id,
                    'department_id'          => $user->department_id,
                    'score'                  => $user->score,
                    'is_verified'            => (bool)$user->is_verified,
                    'is_active'              => (bool)$user->is_active,
                    'is_banned'              => (bool)$user->is_banned,
                    'false_complaints_count' => $user->false_complaints_count,
                ],
            ],
        ], 201);
    }

    /**
     * تفعيل الإيميل والحصول على التوكن
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->code)
                    ->where('verification_expires_at', '>=', now())
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        $user->update([
            'is_verified'             => true,
            'verification_code'       => null,
            'verification_expires_at' => null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'data'    => [
                'user' => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'role_id' => $user->role_id,
                ],
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * تسجيل الدخول بالاسم
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('name', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'user' => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role_id'       => $user->role_id,
                    'score'         => $user->score,
                    'is_verified'   => (bool)$user->is_verified,
                ],
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully.'], 200);
    }
}