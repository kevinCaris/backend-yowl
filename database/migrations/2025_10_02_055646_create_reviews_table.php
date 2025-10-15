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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            //$table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->text('product_link')->nullable();
            $table->string('product_image')->nullable();
            $table->text('content');
            $table->string('tag')->nullable();
            $table->integer('rating')->default(0); // Note sur 5
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
