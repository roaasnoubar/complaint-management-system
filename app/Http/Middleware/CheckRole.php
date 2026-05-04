<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user()?->load('role');    
        // إذا لم يكن هناك مستخدم مسجل دخول أصلاً
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    
        foreach ($roles as $role) {
            // الفحص الأول: هل الاسم مطابق؟ (admin, manager, etc)
            if ($user->role?->name === $role) {
                return $next($request);
            }
    
            if ($role === 'admin' && $user->role?->level === 0) return $next($request);
            if ($role === 'manager' && $user->role?->level === 1) return $next($request);
            if ($role === 'dept_manager' && $user->role?->level === 2) return $next($request);
            if ($role === 'employee' && $user->role?->level === 3) return $next($request);
        }
    
        // إذا فشل كل ما سبق، أرجعي هذه الرسالة للتأكد من هوية المستخدم الحالية
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Admin or Authority Manager only.',
            'debug_info' => [
                'your_role_name' => $user->role?->name,
                'your_role_level' => $user->role?->level
            ]
        ], 403);
    }
}