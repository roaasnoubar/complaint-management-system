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
    
    if (!$user || !$user->role) {
        return response()->json(['message' => 'علاقة الدور غير موجودة لهذا المستخدم'], 401);
    }

    // جلب اسم الدور ومستواه من قاعدة البيانات
$userRoleName = strtolower($user->role->name); 
$userLevel = intval($user->role->level);    

foreach ($roles as $role) {
    $roleLower = strtolower($role);

    if ($userRoleName === $roleLower) {
        return $next($request);
    }

    if ($roleLower === 'admin' && $userLevel === 0) return $next($request);
    if ($roleLower === 'manager' && $userLevel === 1) return $next($request); 
    if ($roleLower === 'authority_manager' && $userLevel === 1) return $next($request);
    if ($roleLower === 'dept_manager' && $userLevel === 2) return $next($request);
    if ($roleLower === 'employee' && $userLevel === 3) return $next($request);
}
  
return response()->json([
    'success' => false,
    'message' => 'Unauthorized access.',
    'debug' => [
        'message' => 'عذراً، ليس لديك الصلاحية للقيام بهذا الإجراء.'
    ]
], 403);
}
}
