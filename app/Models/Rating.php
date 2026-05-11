<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    // التأكد من اسم الجدول الصحيح في قاعدة البيانات
    protected $table = 'ratings';

    protected $fillable = [
        'complain_id', // تأكدي أنها مكتوبة هكذا
        'user_id',
        'authority_id',
        'response_speed_score',
        'comment',
    ];

    protected $casts = [
        'response_speed_score' => 'integer',
        'complaint_validity'   => 'boolean',
    ];

    // العلاقة مع الشكوى
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    // المستخدم (صاحب الشكوى)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // الجهة المسؤولة (الموظف المُقيّم)
    public function authority(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authority_id');
    }
}