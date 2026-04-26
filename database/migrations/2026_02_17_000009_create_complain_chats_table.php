<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // نستخدم if (!Schema::hasTable) لضمان عدم محاولة الإنشاء إذا وجد الجدول لسبب ما
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chat_id'); 
                $table->unsignedBigInteger('sender_id');
                $table->text('message')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_type')->nullable();
                $table->timestamp('sent_at')->useCurrent();
                $table->timestamps();
            });

            // إضافة الروابط بعد التأكد من وجود الجداول الأب
            Schema::table('chat_messages', function (Blueprint $table) {
                // ملاحظة: تأكدي أن جدول complain_chats موجود بالفعل قبل تشغيل هذا الملف
                if (Schema::hasTable('complain_chats')) {
                    $table->foreign('chat_id')->references('id')->on('complain_chats')->onDelete('cascade');
                }
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};