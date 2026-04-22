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
        Schema::create('complains', function (Blueprint $table) {
            $table->id();
            // رقم الشكوى الفريد
            $table->string('complain_number')->unique();
            
            // بيانات مقدم الشكوى
            $table->string('full_name'); 
            $table->unsignedBigInteger('user_id'); 
            
            // الربط مع الجهة والقسم المختص
            $table->unsignedBigInteger('auth_id'); 
            $table->unsignedBigInteger('department_id'); 
            $table->unsignedBigInteger('current_department_id')->nullable(); 

            // --- الحقول المضافة لحل مشكلة Unknown Column ---
            $table->unsignedBigInteger('category_id')->nullable(); // لتصنيف نوع الشكوى (تقنية، إدارية...)
            $table->string('priority')->default('normal'); // الأولوية التي كان يبحث عنها الكود
            $table->string('location')->nullable(); // مكان المشكلة (اختياري)
            // ----------------------------------------------
            
            // محتوى الشكوى
            $table->string('title');
            $table->text('description');
            
            // حالة الشكوى وتفاصيل المعالجة
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])->default('Pending');
            $table->boolean('is_valid')->default(true);
            $table->integer('assigned_level')->default(3); 
            
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // الفهارس والروابط الخارجية
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('auth_id')->references('id')->on('authorities')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            
            $table->index(['user_id', 'auth_id', 'status']); // فهرس مركب لسرعة البحث
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complains');
    }
};