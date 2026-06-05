<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Complain;
use Carbon\Carbon;

class CheckEscalation
{
  
    public function handle(Request $request, Closure $next): Response
    {
        $now = Carbon::now('Asia/Damascus');
        
        $delay = (clone $now)->subMinute()->addSeconds(5);
    
        Complain::where('status', 'Pending')
            ->where('assigned_level', 2)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 1,
                'assigned_at'    => $now,
                'updated_at'     => $now
            ]);
    
        Complain::where('status', 'Pending')
            ->where('assigned_level', 3)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 2,
                'assigned_at'    => $now,
                'updated_at'     => $now
            ]);
    
        return $next($request);
    }
}