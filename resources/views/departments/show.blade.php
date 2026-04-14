@extends('layouts.app')
@section('title', $department->name)
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $department->name }}</h1>
    <a href="{{ route('departments.edit', $department) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl space-y-2">
    <p><strong>Description:</strong> {{ $department->description ?? '-' }}</p>
    <p><strong>Authority:</strong> {{ $department->authority?->name ?? '-' }}</p>
    <p><strong>Status:</strong> {{ $department->is_active ? 'Active' : 'Inactive' }}</p>
    <p><strong>Users:</strong> {{ $department->users->count() }}</p>
    <p><strong>Complaints:</strong> {{ $department->complaints->count() }}</p>
</div>
<div class="mt-4"><a href="{{ route('departments.index') }}" class="text-blue-600 hover:underline">&larr; Back</a></div>
@endsection
