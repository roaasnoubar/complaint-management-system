<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    // --- دالة إنشاء مستخدم جديد (التي يحتاجها الأدمن لإضافة مدراء الجهات) ---
    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();
    
        // 1. السماح فقط للأدمن أو مدير الجهة
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin or Authority Manager only.',
            ], 403);
        }
    
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'nullable|string|unique:users,phone',
            'password'      => 'required|string|min:6',
            'role_id'       => 'required|exists:roles,id',
            'authority_id'  => 'nullable|exists:authorities,id', 
            'department_id' => 'nullable|exists:departments,id',
        ]);
    
        // 2. منطق الحماية: إذا كان "مدير جهة"، نجبر النظام على ربط المستخدم بجهته تلقائياً
        // الأدمن يختار أي جهة، لكن مدير الجامعة لا يضيف إلا لجامعته
        $finalAuthorityId = $currentUser->isAdmin() ? ($validated['authority_id'] ?? null) : $currentUser->authority_id;
    
        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'username'      => explode('@', $validated['email'])[0],
            'phone'         => $validated['phone'] ?? null,
            'password'      => Hash::make($validated['password']),
            'role_id'       => $validated['role_id'],
            'authority_id'  => $finalAuthorityId, // الربط المحمي
            'department_id' => $validated['department_id'] ?? null,
            'is_active'     => true,
            'is_verified'   => true,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $user->load(['role', 'authority', 'department']),
        ], 201);
    }
    public function index(Request $request): JsonResponse
{
    $currentUser = $request->user();

    // السماح للأدمن والمدير
    if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized.',
        ], 403);
    }

    $query = User::with(['role', 'authority', 'department']);

    // حماية البيانات: إذا كان مدير جهة، نعرض له فقط التابعين لجهته (مثل جامعة الشام)
    if ($currentUser->isManager()) {
        $query->where('authority_id', $currentUser->authority_id);
    }

    // الفلاتر المتبقية (كما هي في كودك)
    if ($request->has('role_id')) {
        $query->where('role_id', $request->role_id);
    }

    if ($request->has('authority_id') && $currentUser->isAdmin()) {
        $query->where('authority_id', $request->authority_id);
    }

    if ($request->has('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    // ... باقي كود الفلترة والبحث كما هو لديكِ ...

    $users = $query->latest()->paginate(10);
    
    // ... تحويل البيانات (Transform) كما هو لديكِ ...

    return response()->json([
        'success' => true,
        'data'    => $users,
    ], 200);
}

    public function show(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::with(['role', 'authority', 'department'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'verification' => $user->verification,
                'score'        => $user->score,
                'is_active'    => $user->is_active,
                'role'         => $user->role ? [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name,
                    'level' => $user->role->level,
                ] : null,
                'authority'    => $user->authority ? [
                    'id'   => $user->authority->id,
                    'name' => $user->authority->name,
                ] : null,
                'department'   => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
                'created_at'   => $user->created_at,
            ],
        ], 200);
    }

    public function assignRole(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'authority_id'  => 'nullable|exists:authorities,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update([
            'role_id'       => $request->role_id,
            'authority_id'  => $request->authority_id,
            'department_id' => $request->department_id,
        ]);

        $user->load(['role', 'authority', 'department']);

        return response()->json([
            'success' => true,
            'message' => 'User role assigned successfully.',
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'role'         => $user->role ? [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                ] : null,
                'authority'    => $user->authority ? [
                    'id'   => $user->authority->id,
                    'name' => $user->authority->name,
                ] : null,
                'department'   => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
            ],
        ], 200);
    }

    public function toggleActive(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.',
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }
}