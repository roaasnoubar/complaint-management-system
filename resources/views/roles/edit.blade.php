@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Role</h1>
<form method="POST" action="{{ route('roles.update', $role) }}" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div><label class="block text-sm font-medium mb-1">Type *</label>
        <input type="text" name="type" value="{{ old('type', $role->type) }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-1">Level *</label>
        <input type="number" name="level" value="{{ old('level', $role->level) }}" min="1" required class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-2">Permissions</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach($permissions as $p)
            <label class="flex items-center gap-2">
                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ in_array($p->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                {{ $p->name }}
            </label>
            @endforeach
        </div></div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
        <a href="{{ route('roles.show', $role) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
