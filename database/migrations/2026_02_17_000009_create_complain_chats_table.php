<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complain_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complain_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_open')->default(true);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('complain_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complain_chats');
    }
};