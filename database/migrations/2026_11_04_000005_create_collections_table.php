<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('prompts_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index('is_public');
        });

        Schema::create('collection_prompt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['collection_id', 'prompt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_prompt');
        Schema::dropIfExists('collections');
    }
};
