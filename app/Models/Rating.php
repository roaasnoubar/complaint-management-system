<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $table = 'ratings'; 

    protected $fillable = [
    'complain_id',
    'user_id',
    'auth_id',
    'response_speed_score',
    'rating', 
    'comment',
    ];

    // تحويل أنواع البيانات تلقائياً لمنع أخطاء السلسلة النصية
    protected $casts = [
        'response_speed_score' => 'integer',
    ];

    /**
     * العلاقة: التقييم ينتمي إلى شكوى واحدة
     */
    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    /**
     * االطالب صاحب التقييم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     *التقييم ينتمي إلى الجهة التي تم تقييمها
     */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'auth_id');
    }
}