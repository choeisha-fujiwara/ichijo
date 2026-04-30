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
            if (!Schema::hasColumn('articles', 'manager')) {
                $table->string('manager')->nullable()->after('memo');
            }

            if (!Schema::hasColumn('articles', 'venue_id')) {
                $table->foreignId('venue_id')
                    ->nullable()
                    ->unique()
                    ->after('manager')
                    ->constrained('article_venues')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'venue_id')) {
                $table->dropConstrainedForeignId('venue_id');
            }

            if (Schema::hasColumn('articles', 'manager')) {
                $table->dropColumn('manager');
            }
        });
    }
};
