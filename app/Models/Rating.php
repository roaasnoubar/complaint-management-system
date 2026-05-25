<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    // ⚠️ التعديل السحري هنا: إضافة حرف t ليطابق اسم جدولكِ الحقيقي في قاعدة البيانات
    protected $table = 'rattings'; 

    // الحقول المسموح بتعبئتها بأمان
    protected $fillable = [
        'complain_id',
        'user_id',
        'authority_id',
        'response_speed_score',
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
     * العلاقة: التقييم ينتمي إلى مستخدم (الطالب صاحب التقييم)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة: التقييم ينتمي إلى الجهة التي تم تقييمها
     */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }
}