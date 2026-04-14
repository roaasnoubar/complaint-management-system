@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <p class="text-gray-600 dark:text-gray-400">Complaint Management System Overview</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Total Complaints</p>
        <p class="text-2xl font-bold">{{ $stats['complaints_total'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ $stats['complaints_pending'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Resolved</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['complaints_resolved'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Users</p>
        <p class="text-2xl font-bold">{{ $stats['users_count'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Departments</p>
        <p class="text-2xl font-bold">{{ $stats['departments_count'] }}</p>
    </div>
</div>

<div>
    <h2 class="text-lg font-semibold mb-4">Recent Complaints</h2>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentComplaints as $complaint)
                <tr>
                    <td class="px-4 py-2">{{ $complaint->title }}</td>
                    <td class="px-4 py-2">{{ $complaint->user?->name ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($complaint->status === 'resolved') bg-green-100 text-green-800
                            @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ $complaint->status }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $complaint->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('complaints.show', $complaint) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No complaints yet. <a href="{{ route('complaints.create') }}" class="text-blue-600">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
