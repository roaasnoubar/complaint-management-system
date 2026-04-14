<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complain_id');
            $table->unsignedBigInteger('user_id');
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();

            $table->index('complain_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};