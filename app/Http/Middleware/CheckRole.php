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
   // جلب البيانات من علاقة الدور (تأكدي أن المسميات تطابق الـ Response)
$userRoleName = strtolower($user->role->name); // ستكون manager
$userLevel = intval($user->role->level);      // ستكون 1

foreach ($roles as $role) {
    $roleLower = strtolower($role);

    // 1. الفحص بالاسم (هذا ما يجب أن ينجح لـ manager)
    if ($userRoleName === $roleLower) {
        return $next($request);
    }

    // 2. الفحص بالمستوى (للتأكيد الإضافي)
    if ($roleLower === 'admin' && $userLevel === 0) return $next($request);
    if ($roleLower === 'manager' && $userLevel === 1) return $next($request); // تأكدي أنها manager هنا
    if ($roleLower === 'authority_manager' && $userLevel === 1) return $next($request);
    if ($roleLower === 'dept_manager' && $userLevel === 2) return $next($request);
    if ($roleLower === 'employee' && $userLevel === 3) return $next($request);
}
    // في حال فشل كل الشروط، ستظهر لكِ هذه الرسالة مع تفاصيل "لماذا فشل"
   // استبدلي آخر رسالة خطأ في ملف CheckRole بهذا:
return response()->json([
    'success' => false,
    'message' => 'Unauthorized access.',
    'debug' => [
        'user_id' => $user->id,
        'user_role_name_in_db' => $userRoleName,
        'user_level_in_db' => $userLevel,
        'roles_required_by_route' => $roles
    ]
], 403);
}
}
