<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $stats = [
            'complaints_total' => Complaint::count(),
            'complaints_pending' => Complaint::where('status', 'pending')->count(),
            'complaints_resolved' => Complaint::where('status', 'resolved')->count(),
            'users_count' => User::count(),
            'departments_count' => Department::count(),
        ];

        $recentComplaints = Complaint::with('user')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentComplaints'));
    }
}
