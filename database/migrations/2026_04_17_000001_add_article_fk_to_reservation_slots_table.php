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
        if (!Schema::hasTable('reservation_slots') || !Schema::hasTable('articles')) {
            return;
        }

        if (!Schema::hasColumn('reservation_slots', 'article_id')) {
            return;
        }

        try {
            Schema::table('reservation_slots', function (Blueprint $table) {
                $table->foreign('article_id')
                    ->references('id')
                    ->on('articles')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // The constraint may already exist in some environments.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('reservation_slots')) {
            return;
        }

        Schema::table('reservation_slots', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_slots', 'article_id')) {
                try {
                    $table->dropForeign(['article_id']);
                } catch (\Throwable $e) {
                    // The constraint may not exist in some environments.
                }
            }
        });
    }
};
