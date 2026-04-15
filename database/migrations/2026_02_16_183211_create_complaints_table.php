<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complains', function (Blueprint $table) {
            $table->id();
            $table->string('complain_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('auth_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('current_department_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['Pending', 'In Progress', 'Resolved'])->default('Pending');
            $table->boolean('is_valid')->default(true);
            $table->integer('assigned_level')->default(3);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('auth_id');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complains');
    }
};