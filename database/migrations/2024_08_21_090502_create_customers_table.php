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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('shop_name');
            $table->string('state_topic')->nullable();
            $table->string('state_admin')->nullable();
            $table->string('state_manager')->nullable();
            $table->string('state_shop')->nullable();
            $table->text('comment_admin')->nullable();
            $table->text('comment_manager')->nullable();
            $table->text('comment_shop')->nullable();
            $table->text('email')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('name')->nullable();
            $table->string('tel')->nullable();
            $table->string('zipcode')->nullable();
            $table->text('address')->nullable();
            $table->text('q01')->nullable();
            $table->text('q02')->nullable();
            $table->text('q03')->nullable();
            $table->text('q04')->nullable();
            $table->text('q05')->nullable();
            $table->text('q06')->nullable();
            $table->text('q07')->nullable();
            $table->text('q08')->nullable();
            $table->text('q09')->nullable();
            $table->text('q10')->nullable();
            $table->text('q11')->nullable();
            $table->text('q12')->nullable();
            $table->text('q13')->nullable();
            $table->text('q14')->nullable();
            $table->text('q15')->nullable();
            $table->text('q16')->nullable();
            $table->text('q17')->nullable();
            $table->text('q18')->nullable();
            $table->text('q19')->nullable();
            $table->text('q20')->nullable();
            $table->text('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
