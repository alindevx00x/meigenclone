<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prompt_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('cover_image_path')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->boolean('is_free')->default(false);
            $table->unsignedInteger('prompt_count')->default(0);
            $table->unsignedInteger('downloads_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('status', 24)->default('active'); // active, draft
            $table->timestamps();

            $table->index('is_featured');
            $table->index('price');
        });

        Schema::create('prompt_pack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['prompt_pack_id', 'prompt_id']);
        });

        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prompt_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('currency', 8)->default('USD');
            $table->text('message')->nullable();
            $table->string('status', 24)->default('completed');
            $table->string('transaction_reference')->nullable();
            $table->timestamps();

            $table->index('creator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
        Schema::dropIfExists('prompt_pack_items');
        Schema::dropIfExists('prompt_packs');
    }
};
