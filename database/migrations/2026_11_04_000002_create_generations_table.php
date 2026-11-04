<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prompt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32)->default('mock'); // replicate, openai, mock
            $table->string('model', 64)->default('flux-schnell'); // flux-schnell, flux-1.1-pro, dall-e-3
            $table->text('prompt_text');
            $table->string('aspect_ratio', 16)->default('1:1'); // 1:1, 16:9, 9:16, 4:5
            $table->string('output_image_path')->nullable();
            $table->string('status', 24)->default('pending'); // pending, completed, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('credits_used')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
