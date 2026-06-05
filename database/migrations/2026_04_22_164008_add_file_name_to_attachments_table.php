<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // يجب أن يكون التعديل داخل الـ Closure الخاصة بالجدول
        Schema::table('attachments', function (Blueprint $table) {
            // نتحقق أولاً لمنع حدوث خطأ إذا كان العمود موجوداً بالفعل
            if (!Schema::hasColumn('attachments', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};