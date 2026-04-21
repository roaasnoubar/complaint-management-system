@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
    <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl space-y-2">
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Phone:</strong> {{ $user->phone ?? '-' }}</p>
    <p><strong>Role:</strong> {{ $user->role?->type ?? '-' }}</p>
    <p><strong>Department:</strong> {{ $user->department?->name ?? '-' }}</p>
    <p><strong>Authority:</strong> {{ $user->authority?->name ?? '-' }}</p>
    <p><strong>Status:</strong> {{ $user->is_active ? 'Active' : 'Inactive' }}</p>
    <p><strong>Complaints:</strong> {{ $user->complaints->count() }}</p>
</div>
<div class="mt-4"><a href="{{ route('users.index') }}" class="text-blue-600 hover:underline">&larr; Back</a></div>
@endsection
