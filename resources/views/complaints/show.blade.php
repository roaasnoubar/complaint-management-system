@extends('layouts.app')

@section('title', $complaint->title)

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $complaint->title }}</h1>
        <p class="text-gray-500">#{{ $complaint->id }} · {{ $complaint->created_at->format('M d, Y H:i') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('complaints.edit', $complaint) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
        <a href="{{ route('chats.show', $complaint) }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Chat</a>
        <form method="POST" action="{{ route('complaints.destroy', $complaint) }}" onsubmit="return confirm('Delete this complaint?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Description</h2>
            <p class="whitespace-pre-wrap">{{ $complaint->description }}</p>
        </div>
        @if($complaint->attachments->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Attachments</h2>
            <ul>
                @foreach($complaint->attachments as $att)
                <li class="flex justify-between py-1">
                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="text-blue-600">{{ basename($att->file_path) }}</a>
                    <form method="POST" action="{{ route('attachments.destroy', $att) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Status</p>
            <p><span class="px-2 py-1 rounded-full
                @if($complaint->status === 'resolved') bg-green-100 text-green-800
                @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800
                @else bg-amber-100 text-amber-800 @endif">{{ $complaint->status }}</span></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Created by</p>
            <p>{{ $complaint->user?->name ?? '-' }}</p>
            <p class="text-sm">{{ $complaint->user?->email ?? '' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Department</p>
            <p>{{ $complaint->department?->name ?? '-' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Authority</p>
            <p>{{ $complaint->authority?->name ?? '-' }}</p>
        </div>

        @if($complaint->status === 'resolved' && $complaint->authority_id)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="font-semibold mb-2">Rate Authority</h3>
            <form method="POST" action="{{ route('ratings.store') }}">
                @csrf
                <input type="hidden" name="complain_id" value="{{ $complaint->id }}">
                <input type="hidden" name="authority_id" value="{{ $complaint->authority_id }}">
                <div class="flex gap-1 mb-2">
                    @for($i=1;$i<=5;$i++)
                    <label><input type="radio" name="response_speed_score" value="{{ $i }}" {{ old('response_speed_score') == $i ? 'checked' : '' }}> {{ $i }}</label>
                    @endfor
                </div>
                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Submit Rating</button>
            </form>
        </div>
        @endif

        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            @csrf
            <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
            <label class="block text-sm font-medium mb-1">Add Attachment</label>
            <input type="file" name="file" class="block mb-2">
            <button type="submit" class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Upload</button>
        </form>
    </div>
</div>
@endsection
