<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEscalation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // هذا الكود سيُنفذ في الخلفية مع كل طلب للموقع
        $delay = \Carbon\Carbon::now('UTC')->subMinute();
    
        // تصعيد لمدير الجهة
        \App\Models\Complain::where('assigned_level', 2)
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 1,
                'assigned_at' => now(),
                'updated_at' => now()
            ]);
    
        // تصعيد لمدير القسم
        \App\Models\Complain::where('assigned_level', 3)
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 2,
                'assigned_at' => now(),
                'updated_at' => now()
            ]);
    
        return $next($request);
    }
}
