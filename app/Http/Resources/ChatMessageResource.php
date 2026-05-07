<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id'         => $this->id,
        'message'    => $this->message,
        // هذا السطر يحول المسار لرابط كامل يفتح في الأندرويد
        'file_url'   => $this->file_path ? asset('storage/' . $this->file_path) : null,
        'file_type'  => $this->file_type,
        
        // --- السطر الذي طلبته لإظهار حالة القراءة ---
        'is_read'    => (bool) $this->is_read, 
        
        'sent_at'    => $this->created_at->format('Y-m-d H:i:s'),
        'sender'     => [
            'id'   => $this->sender->id,
            'name' => $this->sender->name,
        ],
    ];
}
}
