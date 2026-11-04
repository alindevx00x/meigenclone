<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('srefs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title');
            $table->string('category', 64)->default('Cinematic');
            $table->text('description')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->json('color_palette')->nullable(); // array of hex colors e.g. ["#0B0C10", "#66FCF1", "#45A29E"]
            $table->json('tags')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('copies_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('category');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srefs');
    }
};
