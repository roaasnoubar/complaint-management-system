@extends('layouts.app')
@section('title', $authority->name)
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $authority->name }}</h1>
    <a href="{{ route('authorities.edit', $authority) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl space-y-2">
    <p><strong>Description:</strong> {{ $authority->description ?? '-' }}</p>
    <p><strong>Departments:</strong> {{ $authority->departments->count() }}</p>
    <p><strong>Users:</strong> {{ $authority->users->count() }}</p>
    <p><strong>Complaints:</strong> {{ $authority->complaints->count() }}</p>
</div>
<div class="mt-4"><a href="{{ route('authorities.index') }}" class="text-blue-600 hover:underline">&larr; Back</a></div>
@endsection
