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
        Schema::table('complains', function (Blueprint $table) {
            // إضافة الحقل وربطه بجدول الهيئات
            // تأكدي أن اسم الجدول هو 'authorities' أو عدليه للاسم الصحيح عندك
            $table->unsignedBigInteger('auth_id')->nullable()->after('user_id');
            $table->foreign('auth_id')->references('id')->on('authorities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complains', function (Blueprint $table) {
            $table->dropForeign(['auth_id']);
            $table->dropColumn('auth_id');
        });
    }
};