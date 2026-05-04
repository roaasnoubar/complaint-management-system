<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'complain_id',
        'user_id',
        'file_path',
        'file_type',
        'file_name'
    ];
    protected $appends = ['full_url'];

    // تعريف كيف يتم بناء هذا الرابط
    public function getFullUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}