@extends('layouts.app')
@section('title', 'New Role')
@section('content')
<h1 class="text-2xl font-bold mb-6">Add Role</h1>
<form method="POST" action="{{ route('roles.store') }}" class="max-w-2xl space-y-4">
    @csrf
    <div><label class="block text-sm font-medium mb-1">Type *</label>
        <input type="text" name="type" value="{{ old('type') }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800" placeholder="e.g. citizen, employee"></div>
    <div><label class="block text-sm font-medium mb-1">Level *</label>
        <input type="number" name="level" value="{{ old('level', 1) }}" min="1" required class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-2">Permissions</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach($permissions as $p)
            <label class="flex items-center gap-2">
                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" {{ in_array($p->id, old('permissions', [])) ? 'checked' : '' }}>
                {{ $p->name }}
            </label>
            @endforeach
        </div></div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
        <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
