@extends('layouts.app')
@section('title', 'Departments')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Departments</h1>
    <a href="{{ route('departments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Department</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700"><tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Authority</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($departments as $dept)
            <tr>
                <td class="px-4 py-2">{{ $dept->name }}</td>
                <td class="px-4 py-2">{{ $dept->authority?->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ $dept->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('departments.show', $dept) }}" class="text-blue-600 hover:underline mr-2">View</a>
                    <a href="{{ route('departments.edit', $dept) }}" class="text-blue-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No departments.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $departments->withQueryString()->links() }}</div>
@endsection
