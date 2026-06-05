<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ratings')) {
            Schema::create('ratings', function (Blueprint $table) {
                $table->id();
                
                $table->unsignedBigInteger('complain_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('auth_id');

                $table->integer('rating');
                $table->text('comment')->nullable();
                $table->integer('response_speed_score')->default(0);
                $table->timestamps();

                // الفهارس والقيود الفريدة
                $table->unique(['complain_id', 'user_id']);
                $table->index('auth_id');

             
                if (DB::connection()->getDriverName() !== 'sqlite') {
                    $table->foreign('complain_id')->references('id')->on('complains')->onDelete('cascade');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->foreign('auth_id')->references('id')->on('authorities')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};