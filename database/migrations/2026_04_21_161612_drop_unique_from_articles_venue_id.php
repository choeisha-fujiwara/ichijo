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
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique('articles_venue_id_unique');
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('venue_id')
                ->references('id')
                ->on('article_venues')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->unique('venue_id');
            $table->foreign('venue_id')
                ->references('id')
                ->on('article_venues')
                ->nullOnDelete();
        });
    }
};
