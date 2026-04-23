<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon; // تأكدي من استدعاء Carbon للتعامل مع الوقت

class Complain extends Model
{
    protected $table = 'complains';

    // 1. إضافة الحقول الجديدة للـ fillable
    protected $fillable = [
        'full_name',   
        'complain_number',
        'user_id',
        'auth_id',
        'department_id',
        'priority', 
        'current_department_id',
        'title',
        'description',
        'status',
        'is_valid',
        'assigned_level',
        'assigned_at',
        'resolved_at',
    ];

    // 2. إخبار لارافيل بإضافة الحقل الوهمي (المقروء) للـ JSON تلقائياً
    protected $appends = ['created_at_human'];

    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_valid'    => 'boolean',
    ];

    // الثوابت (تبقى كما هي)
    const STATUS_PENDING     = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_RESOLVED    = 'Resolved';
    const STATUS_REJECTED    = 'Rejected'; // <--- أضيفي هذا السطر هنا (ضروري جداً)

    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING     => [self::STATUS_IN_PROGRESS, self::STATUS_REJECTED],
        self::STATUS_IN_PROGRESS => [self::STATUS_RESOLVED, self::STATUS_REJECTED],
        self::STATUS_RESOLVED    => [],
        self::STATUS_REJECTED    => [],
    ];

    // 3. دالة التاريخ المقروء (Human Readable Time)
    public function getCreatedAtHumanAttribute(): string
    {
        // لترجمة الوقت للعربية يمكنك استخدام: return $this->created_at->diffForHumans();
        // مع التأكد من ضبط الـ locale في config/app.php إلى 'ar'
        return $this->created_at->diffForHumans();
    }

    protected static function booted(): void
    {
        static::creating(function (Complain $complain) {
            $complain->complain_number = self::generateComplainNumber();
        });

        static::created(function (Complain $complain) {
            // تأكدي من وجود موديل ComplainChat
            if (class_exists(ComplainChat::class)) {
                ComplainChat::create([
                    'complain_id' => $complain->id,
                    'user_id'     => $complain->user_id,
                    'is_open'     => true,
                ]);
            }
        });

        static::updated(function (Complain $complain) {
            if ($complain->status === self::STATUS_RESOLVED) {
                $chat = ComplainChat::where('complain_id', $complain->id)->first();
                if ($chat && $chat->is_open) {
                    $chat->update([
                        'is_open'   => false,
                        'closed_at' => now(),
                    ]);
                }
            }
        });
    }

    public static function generateComplainNumber(): string
    {
        $year       = now()->year;
        $last       = self::orderBy('id', 'desc')->first();
        $nextNumber = $last ? ($last->id + 1) : 1;
        return 'CMP-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    // العلاقات
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function authority(): BelongsTo { return $this->belongsTo(Authority::class, 'auth_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function attachments(): HasMany { return $this->hasMany(Attachment::class, 'complain_id'); }
    public function chat(): HasOne { return $this->hasOne(ComplainChat::class, 'complain_id'); }

    public function getLevelNameAttribute(): string
    {
        return match($this->assigned_level) {
            3       => 'Employee',
            2       => 'Department Manager',
            1       => 'Head of Organization',
            default => 'Unknown',
        };
    }
}