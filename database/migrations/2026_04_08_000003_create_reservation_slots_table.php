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
        if (Schema::hasTable('reservation_slots')) {
            return;
        }

        Schema::create('reservation_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('reserved_count')->default(0);
            $table->text('memo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_slots');
    }
};
