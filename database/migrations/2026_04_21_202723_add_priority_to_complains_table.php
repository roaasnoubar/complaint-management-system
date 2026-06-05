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
        // التحقق أولاً إذا كان العمود غير موجود، قم بإضافته
        if (!Schema::hasColumn('complains', 'priority')) {
            $table->string('priority')->default('normal')->after('description');
        }
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
