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
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->unsignedBigInteger('reservation_id')->nullable()->change();
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();

            $table->foreignId('article_id')->nullable()->after('reservation_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropColumn('article_id');

            $table->dropForeign(['reservation_id']);
            $table->unsignedBigInteger('reservation_id')->nullable(false)->change();
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
        });
    }
};
