<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // تغيير الاسم من cant_messages إلى chat_messages
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('chat_id');
        $table->unsignedBigInteger('sender_id');
        $table->text('message')->nullable();
        $table->string('file_path')->nullable();
        $table->string('file_type')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();

        // إضافة العلاقات (Foreign Keys) لضمان سلامة البيانات
        $table->foreign('chat_id')->references('id')->on('complain_chats')->onDelete('cascade');
        $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

        $table->index('chat_id');
        $table->index('sender_id');
    });
}

    public function down(): void
    {
        Schema::dropIfExists('cant_messages');
    }
};