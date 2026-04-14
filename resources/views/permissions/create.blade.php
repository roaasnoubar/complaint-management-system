@extends('layouts.app')
@section('title', 'New Permission')
@section('content')
<h1 class="text-2xl font-bold mb-6">Add Permission</h1>
<form method="POST" action="{{ route('permissions.store') }}" class="max-w-2xl space-y-4">
    @csrf
    <div><label class="block text-sm font-medium mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800" placeholder="e.g. create_complaint"></div>
    <div><label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded dark:bg-gray-800">{{ old('description') }}</textarea></div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
        <a href="{{ route('permissions.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
