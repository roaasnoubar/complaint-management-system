<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;               // هاد السطر اللي ناقصك (قالب الرسالة)
class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد (مواطن) مع كود OTP
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

        $user = User::create([
            'name'                    => $validated['name'],
            'username'                => explode('@', $request->email)[0] . '_' . rand(100, 999),
            'phone'                   => $validated['phone'],
            'email'                   => $validated['email'],
            'birthdate'               => $validated['birthdate'],
            'password'                => Hash::make($validated['password']),
            'verification_code'       => $verificationCode,
            'verification_expires_at' => now()->addMinutes(10),
            'is_verified'             => false,
            'role_id'                 => 3, 
            'authority_id'            => 1, 
            'score'                   => 0,
            'is_active'               => true,
            'is_banned'               => false,
            'false_complaints_count'  => 0,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail((string)$verificationCode, $user->name));
        } catch (\Exception $e) {
            Log::error("Mail Error: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for the verification code.',
            'data'    => $user->load('role'),
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
                'user'  => $user->load(['role', 'authority', 'department']),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * تسجيل الدخول (للمواطن والموظف)
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $user->load(['role', 'authority', 'department']),
            ],
        ], 200);
    }

    /**
     * إنشاء حساب موظف جديد (للآدمن)
     */
    public function registerEmployee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|unique:users,username',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'phone'        => 'required|string|unique:users,phone', // أضيفي هذا السطر
            'authority_id' => 'required|exists:authorities,id',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'username'     => $validated['username'],
            'email'        => $validated['email'],
            'password'     => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'phone'        => $request->phone ?? ('09' . rand(10000000, 99999999)),
            'role_id'      => 2, 
            'authority_id' => $validated['authority_id'],
            'is_verified'  => true, 
            'is_active'    => true,
            'score'        => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء حساب الموظف بنجاح.',
            'data'    => $user->load(['role', 'authority'])
        ], 201);
    }

    /**
     * جلب بيانات المستخدم الحالي
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['role','authority', 'department']);
    
        return response()->json([
            'success' => true,
            'data'    => $user
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}