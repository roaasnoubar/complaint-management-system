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
    Schema::create('complain_chats', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('complain_id');
        $table->unsignedBigInteger('user_id'); // المستخدم الذي فتح المحادثة
        $table->boolean('is_open')->default(true);
        $table->timestamps();

        $table->foreign('complain_id')->references('id')->on('complains')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complain_chats');
    }
};
