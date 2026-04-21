@extends('layouts.app')
@section('title', 'Edit Permission')
@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Permission</h1>
<form method="POST" action="{{ route('permissions.update', $permission) }}" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div><label class="block text-sm font-medium mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $permission->name) }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded dark:bg-gray-800">{{ old('description', $permission->description) }}</textarea></div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
        <a href="{{ route('permissions.show', $permission) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
