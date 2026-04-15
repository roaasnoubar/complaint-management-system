<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // دمجنا الاسم هنا ليتوافق مع الموديل
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->date('birthdate')->nullable();
            $table->string('password');
            
            // نظام التحقق
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            
            // الربط مع الجداول الأخرى
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('authority_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            
            // إحصائيات وحالة الحساب
            $table->integer('score')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->rememberToken();
            $table->timestamps();

            // الفهارس لتسريع البحث
            $table->index('role_id');
            $table->index('authority_id');
            $table->index('department_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};