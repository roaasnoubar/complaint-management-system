<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            
            // الربط مع جدول الشكاوى (بالاسم الصحيح لديكِ complains)
            $table->foreignId('complain_id')->constrained('complains')->onDelete('cascade');
            
            // الربط مع صاحب التقييم (المواطن)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // الربط مع الجهة التي تم تقييمها
            $table->foreignId('auth_id')->constrained('authorities')->onDelete('cascade');

            // حقول التقييم الأساسية
            $table->integer('rating'); // من 1 إلى 5 نجوم
            $table->text('comment')->nullable(); // ملاحظات إضافية
            
            // حقول إحصائية اختيارية (للمستقبل)
            $table->integer('response_speed_score')->default(0); 
            
            $table->timestamps();

            // منع التكرار: لا يمكن للمستخدم تقييم نفس الشكوى أكثر من مرة
            $table->unique(['complain_id', 'user_id']);
            
            // فهارس لتحسين سرعة التقارير والإحصائيات
            $table->index('auth_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};