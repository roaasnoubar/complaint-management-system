<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rattings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complain_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('authority_id');
            $table->integer('response_speed_score')->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['complain_id', 'user_id']);
            $table->index('authority_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rattings');
    }
};