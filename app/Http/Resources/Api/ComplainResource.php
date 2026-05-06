<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'complain_number' => $this->complain_number,
        'status' => $this->status,
        'notes' => $this->notes, // هذا هو الحقل الذي كان ينقصنا
        'full_name' => $this->full_name,
        'description' => $this->description,
        'can_chat' => $this->can_chat,
        'resolved_at' => $this->resolved_at,
        'user' => $this->whenLoaded('user'), // لضمان تحميل بيانات المستخدم
    ];
}
}
