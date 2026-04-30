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
        Schema::table('article_venues', function (Blueprint $table) {
            if (Schema::hasColumn('article_venues', 'article_id')) {
                $table->dropConstrainedForeignId('article_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_venues', function (Blueprint $table) {
            if (!Schema::hasColumn('article_venues', 'article_id')) {
                $table->foreignId('article_id')
                    ->nullable()
                    ->unique()
                    ->after('id')
                    ->constrained('articles')
                    ->cascadeOnDelete();
            }
        });
    }
};
