<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تعطيل فحص العلاقات مؤقتاً
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            
            // نستخدم النوع الخام لضمان التطابق مع الأنظمة القديمة والجديدة
            $table->unsignedBigInteger('chat_id')->index();
            $table->unsignedBigInteger('sender_id')->index();

            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            // إضافة الربط بشكل صريح
            $table->foreign('chat_id')->references('id')->on('complain_chats')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        // إعادة تفعيل فحص العلاقات
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};