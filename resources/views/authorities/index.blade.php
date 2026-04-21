@extends('layouts.app')
@section('title', 'Authorities')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Authorities</h1>
    <a href="{{ route('authorities.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Authority</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700"><tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
            <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($authorities as $auth)
            <tr>
                <td class="px-4 py-2">{{ $auth->name }}</td>
                <td class="px-4 py-2">{{ $auth->departments_count ?? 0 }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('authorities.show', $auth) }}" class="text-blue-600 hover:underline mr-2">View</a>
                    <a href="{{ route('authorities.edit', $auth) }}" class="text-blue-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No authorities.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $authorities->links() }}</div>
@endsection
