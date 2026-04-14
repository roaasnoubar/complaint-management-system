@extends('layouts.app')
@section('title', $permission->name)
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $permission->name }}</h1>
    <a href="{{ route('permissions.edit', $permission) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Edit</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl space-y-2">
    <p><strong>Description:</strong> {{ $permission->description ?? '-' }}</p>
    <p><strong>Roles with this permission:</strong></p>
    <ul class="list-disc list-inside">
        @foreach($permission->roles as $r)<li>{{ $r->type }}</li>@endforeach
    </ul>
</div>
<div class="mt-4"><a href="{{ route('permissions.index') }}" class="text-blue-600 hover:underline">&larr; Back</a></div>
@endsection
