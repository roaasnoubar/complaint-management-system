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
        // إضافة العمود وربطه بجدول المستخدمين
        $table->unsignedBigInteger('processed_by')->nullable()->after('status');
        $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complains', function (Blueprint $table) {
            //
        });
    }
};
