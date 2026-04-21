@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Users</h1>
    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add User</a>
</div>
<form method="GET" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 border rounded dark:bg-gray-800">
    <select name="role_id" class="px-3 py-2 border rounded dark:bg-gray-800">
        <option value="">All roles</option>
        @foreach($roles as $r)<option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->type }}</option>@endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Filter</button>
</form>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700"><tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($users as $user)
            <tr>
                <td class="px-4 py-2">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">{{ $user->role?->type ?? '-' }}</td>
                <td class="px-4 py-2">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:underline mr-2">View</a>
                    <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
