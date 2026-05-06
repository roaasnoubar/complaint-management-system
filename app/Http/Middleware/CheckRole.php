<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = $request->user();
    
    // 1. التأكد من أن المستخدم مسجل دخول وله دور مرتبط به في قاعدة البيانات
    if (!$user || !$user->role) {
        return response()->json(['message' => 'علاقة الدور غير موجودة لهذا المستخدم'], 401);
    }

    // جلب اسم الدور ومستواه من قاعدة البيانات
    $userRoleName = strtolower($user->role->name);
    $userLevel = intval($user->role->level);

    foreach ($roles as $role) {
        // الفحص بناءً على الاسم (الموجود في قاعدة بياناتك)
        if ($userRoleName === strtolower($role)) {
            return $next($request);
        }
    
        // الفحص بناءً على المستوى (لضمان المرونة في مشروع التخرج)
        if ($role === 'admin' && $userLevel === 0) return $next($request);
        if ($role === 'authority_manager' && $userLevel === 1) return $next($request);
        if ($role === 'dept_manager' && $userLevel === 2) return $next($request);
        if ($role === 'employee' && $userLevel === 3) return $next($request);
    }

    // في حال فشل كل الشروط، ستظهر لكِ هذه الرسالة مع تفاصيل "لماذا فشل"
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized access.',
        'debug_info' => [
            'your_role_in_db' => $userRoleName,
            'your_level_in_db' => $userLevel,
            'required_roles' => $roles
        ]
    ], 403);
}
}