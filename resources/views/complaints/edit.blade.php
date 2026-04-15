@extends('layouts.app')

@section('title', 'Edit Complaint')

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Complaint</h1>

<form method="POST" action="{{ route('complaints.update', $complaint) }}" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Title *</label>
        <input type="text" name="title" value="{{ old('title', $complaint->title) }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800">
        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Description *</label>
        <textarea name="description" rows="4" required class="w-full px-3 py-2 border rounded dark:bg-gray-800">{{ old('description', $complaint->description) }}</textarea>
        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status *</label>
        <select name="status" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="pending" {{ old('status', $complaint->status) === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ old('status', $complaint->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="resolved" {{ old('status', $complaint->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Department</label>
            <select name="department_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
                <option value="">Select</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id', $complaint->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Current Department</label>
            <select name="current_department_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
                <option value="">Select</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('current_department_id', $complaint->current_department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Authority</label>
        <select name="authority_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select</option>
            @foreach($authorities as $a)
                <option value="{{ $a->id }}" {{ old('authority_id', $complaint->authority_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_valid" value="1" {{ old('is_valid', $complaint->is_valid) ? 'checked' : '' }}>
            Valid complaint
        </label>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
        <a href="{{ route('complaints.show', $complaint) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
