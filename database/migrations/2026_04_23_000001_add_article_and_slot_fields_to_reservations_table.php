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
        if (!Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'article_id')) {
                $table->foreignId('article_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('articles')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('reservations', 'reservation_slot_id')) {
                $table->foreignId('reservation_slot_id')
                    ->nullable()
                    ->after('article_id')
                    ->constrained('reservation_slots')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('reservations', 'reservation_datetime')) {
                $table->string('reservation_datetime')
                    ->nullable()
                    ->after('reservation_slot_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'article_id')) {
                try {
                    $table->dropForeign(['article_id']);
                } catch (\Throwable $e) {
                    // The foreign key may not exist in some environments.
                }
                $table->dropColumn('article_id');
            }

            if (Schema::hasColumn('reservations', 'reservation_slot_id')) {
                try {
                    $table->dropForeign(['reservation_slot_id']);
                } catch (\Throwable $e) {
                    // The foreign key may not exist in some environments.
                }
                $table->dropColumn('reservation_slot_id');
            }

            if (Schema::hasColumn('reservations', 'reservation_datetime')) {
                $table->dropColumn('reservation_datetime');
            }
        });
    }
};
