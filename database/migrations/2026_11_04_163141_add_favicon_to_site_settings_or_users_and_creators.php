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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('role');
            }
        });

        if (!Schema::hasTable('follows')) {
            Schema::create('follows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['follower_id', 'following_id']);
            });
        }

        if (!Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('follows');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
        });
    }
};
