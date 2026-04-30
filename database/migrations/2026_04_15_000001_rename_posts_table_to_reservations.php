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
        if (Schema::hasTable('posts') && !Schema::hasTable('reservations')) {
            Schema::rename('posts', 'reservations');
        }

        if (
            Schema::hasTable('images')
            && Schema::hasColumn('images', 'post_id')
            && !Schema::hasColumn('images', 'reservation_id')
        ) {
            Schema::table('images', function (Blueprint $table) {
                try {
                    $table->dropForeign(['post_id']);
                } catch (\Throwable $e) {
                    // Foreign key may not exist depending on previous schema.
                }
            });

            Schema::table('images', function (Blueprint $table) {
                $table->renameColumn('post_id', 'reservation_id');
            });

            Schema::table('images', function (Blueprint $table) {
                $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reservations') && !Schema::hasTable('posts')) {
            Schema::rename('reservations', 'posts');
        }

        if (
            Schema::hasTable('images')
            && Schema::hasColumn('images', 'reservation_id')
            && !Schema::hasColumn('images', 'post_id')
        ) {
            Schema::table('images', function (Blueprint $table) {
                try {
                    $table->dropForeign(['reservation_id']);
                } catch (\Throwable $e) {
                    // Foreign key may not exist depending on previous schema.
                }
            });

            Schema::table('images', function (Blueprint $table) {
                $table->renameColumn('reservation_id', 'post_id');
            });

            Schema::table('images', function (Blueprint $table) {
                $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            });
        }
    }
};
