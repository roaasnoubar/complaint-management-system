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
        Schema::create('complains', function (Blueprint $table) {
            $table->id();
            $table->string('complain_number')->unique();
            $table->string('full_name'); 
            $table->unsignedBigInteger('user_id'); 
            
            // توحيد المسمى مع الموديلات السابقة
            $table->unsignedBigInteger('authority_id'); 
            $table->unsignedBigInteger('department_id'); 
            $table->unsignedBigInteger('current_department_id')->nullable(); 
        
            $table->unsignedBigInteger('category_id')->nullable(); 
            $table->string('priority')->default('normal'); 
            $table->string('location')->nullable(); 
            
            $table->string('title');
            $table->text('description');
            
            // التأكد من تطابق الحالات مع الموديل
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])->default('Pending');
            $table->boolean('is_valid')->default(true);
            $table->integer('assigned_level')->default(3); 
            
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        
            // العلاقات
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('current_department_id')->references('id')->on('departments')->onDelete('set null');
            
            $table->index(['user_id', 'authority_id', 'status']); 
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('complains');
    }
};