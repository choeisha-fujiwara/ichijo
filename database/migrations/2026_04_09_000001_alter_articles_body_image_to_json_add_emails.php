<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE articles MODIFY COLUMN body_image JSON NULL');

        Schema::table('articles', function (Blueprint $table) {
            $table->text('emails')->nullable()->after('memo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('emails');
        });

        DB::statement('ALTER TABLE articles MODIFY COLUMN body_image TEXT NULL');
    }
};
