@extends('layouts.app')

@section('title', 'Complaints')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Complaints</h1>
    <a href="{{ route('complaints.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">New Complaint</a>
</div>

<form method="GET" class="mb-4 flex gap-2 flex-wrap">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 border rounded dark:bg-gray-800">
    <select name="status" class="px-3 py-2 border rounded dark:bg-gray-800">
        <option value="">All statuses</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
    </select>
    <select name="department_id" class="px-3 py-2 border rounded dark:bg-gray-800">
        <option value="">All departments</option>
        @foreach($departments as $d)
            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Filter</button>
</form>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($complaints as $complaint)
            <tr>
                <td class="px-4 py-2">{{ $complaint->title }}</td>
                <td class="px-4 py-2">{{ $complaint->user?->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ $complaint->department?->name ?? '-' }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($complaint->status === 'resolved') bg-green-100 text-green-800
                        @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800
                        @else bg-amber-100 text-amber-800 @endif">{{ $complaint->status }}</span>
                </td>
                <td class="px-4 py-2">{{ $complaint->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('complaints.show', $complaint) }}" class="text-blue-600 hover:underline mr-2">View</a>
                    <a href="{{ route('complaints.edit', $complaint) }}" class="text-blue-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No complaints found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $complaints->withQueryString()->links() }}</div>
@endsection
