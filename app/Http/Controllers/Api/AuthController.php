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
use App\Mail\OtpMail;             
class AuthController extends Controller
{
    
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
            'role_id' => \App\Models\Role::where('name', \App\Models\Role::USER)->first()?->id ?? 5,
            'authority_id'            => null, 
            'score'                   => 0,
            'is_active'               => true,
            'is_banned'               => false,
            'false_complaints_count'  => 0,
        ]);
        Mail::to($user->email)->send(new OtpMail((string)$verificationCode, $user->name));
       
        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data'    => $user
        ], 201);
    }

    
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', trim($request->email))
        ->where('verification_code', (string) $request->code)
        ->where('verification_expires_at', '>=', now())
            ->first();
            \Log::info("التحقق من الكود:", [
                'email_input' => $request->email,
                'code_input' => $request->code,
                'user_in_db' => $user ? 'موجود' : 'غير موجود',
                'current_time' => now()
            ]);
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

    
    public function login(Request $request): JsonResponse
{
   
    $credentials = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // 2. تحديد نوع الحقل
    $loginField = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    if (!Auth::attempt([$loginField => $request->username, 'password' => $request->password])) {
        return response()->json([
            'success' => false,
            'message' => 'بيانات الدخول غير صحيحة.'
        ], 401);
    }

    $user = Auth::user();

        if ($user->role_id == 5 && !$user->is_verified) {
            Auth::logout(); 
            
            return response()->json([
                'success' => false,
                'status' => 'verify_account', 
                'message' => 'يرجى تفعيل الحساب أولاً عبر الكود المرسل لإيميلك.'
            ], 403);
    }

  
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
    
    public function registerEmployee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|unique:users,username',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'phone'        => 'required|string|unique:users,phone', 
            'authority_id' => 'required|exists:authorities,id',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'username'     => $validated['username'],
            'email'        => $validated['email'],
            'password'     => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'phone'        => $request->phone ?? ('09' . rand(10000000, 99999999)),
            'role_id' => \App\Models\Role::where('name', 'employee')->first()?->id ?? 2,
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

    
    public function me(Request $request)
    {
        $user = $request->user()->load(['role','authority', 'department']);
    
        return response()->json([
            'success' => true,
            'data'    => $user
        ]);
    }

    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}