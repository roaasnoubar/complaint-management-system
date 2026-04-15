@extends('layouts.app')
@section('title', $role->type)
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $role->type }} (Level {{ $role->level }})</h1>
    <a href="{{ route('roles.edit', $role) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl space-y-2">
    <p><strong>Permissions:</strong></p>
    <ul class="list-disc list-inside">
        @foreach($role->permissions as $p)<li>{{ $p->name }}</li>@endforeach
    </ul>
    <p class="pt-2"><strong>Users:</strong> {{ $role->users->count() }}</p>
</div>
<div class="mt-4"><a href="{{ route('roles.index') }}" class="text-blue-600 hover:underline">&larr; Back</a></div>
@endsection
