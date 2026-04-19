<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 1. الإحصائيات العامة
    public function getStatistics()
    {
        $stats = DB::table('complains')
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when status = 'Pending' then 1 end) as pending")
            ->selectRaw("count(case when status = 'In Progress' then 1 end) as in_progress")
            ->selectRaw("count(case when status = 'Resolved' then 1 end) as resolved")
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_complaints' => $stats->total,
                'status_counts' => [
                    'pending'     => $stats->pending,
                    'in_progress' => $stats->in_progress,
                    'resolved'    => $stats->resolved,
                ]
            ]
        ]);
    }

    // 2. الشكاوى حسب الهيئة (تعديل auth_id بناءً على الصورة)
    public function complaintsByAuthority()
    {
        $data = DB::table('authorities')
            ->leftJoin('complains', 'authorities.id', '=', 'complains.auth_id')
            ->select('authorities.name', DB::raw('count(complains.complain_number) as count'))
            ->groupBy('authorities.id', 'authorities.name')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // 3. الشكاوى حسب القسم
    public function complaintsByDepartment()
    {
        $data = DB::table('departments')
            ->leftJoin('complains', 'departments.id', '=', 'complains.department_id')
            ->select('departments.name', DB::raw('count(complains.complain_number) as count'))
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // 4. الإحصائيات الشهرية
    public function monthlyComplaints()
    {
        $data = DB::table('complains')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw('count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }
}