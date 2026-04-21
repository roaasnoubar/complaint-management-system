@extends('layouts.app')
@section('title', 'New User')
@section('content')
<h1 class="text-2xl font-bold mb-6">Add User</h1>
<form method="POST" action="{{ route('users.store') }}" class="max-w-2xl space-y-4">
    @csrf
    <div><label class="block text-sm font-medium mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800">
        @error('name')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium mb-1">Email *</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800">
        @error('email')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-1">Password *</label>
        <input type="password" name="password" required class="w-full px-3 py-2 border rounded dark:bg-gray-800">
        @error('password')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium mb-1">Confirm Password *</label>
        <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border rounded dark:bg-gray-800"></div>
    <div><label class="block text-sm font-medium mb-1">Role</label>
        <select name="role_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select</option>
            @foreach($roles as $r)<option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->type }}</option>@endforeach
        </select></div>
    <div><label class="block text-sm font-medium mb-1">Department</label>
        <select name="department_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
        </select></div>
    <div><label class="block text-sm font-medium mb-1">Authority</label>
        <select name="authority_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select</option>
            @foreach($authorities as $a)<option value="{{ $a->id }}" {{ old('authority_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>@endforeach
        </select></div>
    <div><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label></div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
