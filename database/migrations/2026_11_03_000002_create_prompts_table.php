<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('prompt_text');
            $table->text('image_url')->nullable();
            $table->string('image_path')->nullable();
            $table->string('model')->default('GPT Image');
            $table->string('status')->default('pending')->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['status', 'is_featured', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
