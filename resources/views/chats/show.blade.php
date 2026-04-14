@extends('layouts.app')

@section('title', 'Chat - ' . $complaint->title)

@section('content')
<div class="mb-4">
    <a href="{{ route('complaints.show', $complaint) }}" class="text-blue-600 hover:underline">&larr; Back to complaint</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-3xl">
    <h2 class="text-xl font-bold mb-4">Chat: {{ $complaint->title }}</h2>

    <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
        @forelse($chat->messages as $msg)
        <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : '' }}">
            <div class="max-w-[80%] p-3 rounded {{ $msg->sender_id === auth()->id() ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                <p class="text-sm text-gray-500">{{ $msg->sender->name ?? 'User' }} · {{ $msg->sent_at->format('M d, H:i') }}</p>
                <p>{{ $msg->message }}</p>
            </div>
        </div>
        @empty
        <p class="text-gray-500 text-center py-4">No messages yet. Start the conversation!</p>
        @endforelse
    </div>

    @if($chat->is_open)
    <form method="POST" action="{{ route('chats.messages.store') }}">
        @csrf
        <input type="hidden" name="chat_id" value="{{ $chat->id }}">
        <div class="flex gap-2">
            <textarea name="message" rows="2" required placeholder="Type your message..." class="flex-1 px-3 py-2 border rounded dark:bg-gray-700"></textarea>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Send</button>
        </div>
    </form>
    @else
    <p class="text-gray-500">This chat is closed.</p>
    @endif

    @if($chat->is_open)
    <form method="POST" action="{{ route('chats.close', $chat) }}" class="mt-2">
        @csrf
        <button type="submit" class="text-sm text-red-600 hover:underline">Close chat</button>
    </form>
    @endif
</div>
@endsection
