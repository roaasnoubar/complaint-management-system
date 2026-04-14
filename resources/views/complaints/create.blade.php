@extends('layouts.app')

@section('title', 'New Complaint')

@section('content')
<h1 class="text-2xl font-bold mb-6">Submit Complaint</h1>

<form method="POST" action="{{ route('complaints.store') }}" class="max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Title *</label>
        <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border rounded dark:bg-gray-800 @error('title') border-red-500 @enderror">
        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Description *</label>
        <textarea name="description" rows="4" required class="w-full px-3 py-2 border rounded dark:bg-gray-800 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Department</label>
        <select name="department_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select department</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Authority</label>
        <select name="authority_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
            <option value="">Select authority</option>
            @foreach($authorities as $a)
                <option value="{{ $a->id }}" {{ old('authority_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit</button>
        <a href="{{ route('complaints.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
    </div>
</form>
@endsection
