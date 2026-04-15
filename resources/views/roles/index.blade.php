@extends('layouts.app')
@section('title', 'Roles')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Roles</h1>
    <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Role</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700"><tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
            <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($roles as $role)
            <tr>
                <td class="px-4 py-2">{{ $role->type }}</td>
                <td class="px-4 py-2">{{ $role->level }}</td>
                <td class="px-4 py-2">{{ $role->users_count ?? 0 }}</td>
                <td class="px-4 py-2">{{ $role->permissions_count ?? 0 }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('roles.show', $role) }}" class="text-blue-600 hover:underline mr-2">View</a>
                    <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No roles.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $roles->links() }}</div>
@endsection
