<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    const TYPE_COMPLAINT_ASSIGNED = 'complaint_assigned';
    const TYPE_COMPLAINT_RESOLVED = 'complaint_resolved';
    const TYPE_COMPLAINT_ESCALATED = 'complaint_escalated';
    const TYPE_COMPLAINT_SUBMITTED = 'complaint_submitted';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_RESOLVED            = 'resolved';
    const TYPE_ESCALATED = 'escalated';
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    // علاقة الإشعار بالمستخدم
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

/*namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الحقول القابلة للتعبئة (Fillable)
     * تأكدي من مطابقتها لأعمدة الجدول في قاعدة البيانات
     */
  /*/*  protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'birthdate',
        'password',
        'verification_code',
        'verification_expires_at',
        'is_verified',
        'role_id',
        'authority_id', // أو auth_id حسب ما استقر عليه جدولك
        'department_id',
        'score',
        'is_active',
        'is_banned',
        'false_complaints_count',
    ];

    /**
     * الحقول المخفية عند تحويل الموديل إلى JSON
     */
   /* protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    /**
     * تحويل أنواع البيانات (Casting)
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthdate' => 'date',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_banned' => 'boolean',
        'verification_expires_at' => 'datetime',
    ];

    // --- العلاقات (Relations) ---

    /**
     * علاقة المستخدم مع الإشعارات الخاصة به
     */
   /* public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    /**
     * علاقة المستخدم مع الدور (الرتبة)
     */
   /* public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * علاقة المستخدم مع الجهة التابع لها (مثل جامعة الشام)
     */
    /*public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    /**
     * علاقة المستخدم مع القسم (مثل قسم المعلوماتية)
     */
    /*public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // --- الدوال المساعدة (Helper Methods) ---

    /**
     * دالة ذكية لإرسال إشعار للمستخدم الحالي وتخزينه في الجدول المخصص
     *
     * @param string $title عنوان الإشعار
     * @param string $message نص الرسالة
     * @param string $type نوع الإشعار (استخدمي الـ Constants من موديل Notification)
     * @param array $data بيانات إضافية (مثل complaint_id)
     * @return \Illuminate\Database\Eloquent\Model
     */
    /*public function sendNotification(string $title, string $message, string $type, array $data = [])
    {
        return $this->notifications()->create([
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'data'    => $data,
            'is_read' => false,
        ]);
    }
}*/
