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
        Schema::table('prompts', function (Blueprint $table) {
            $table->index(['category_id', 'status', 'approved_at'], 'prompts_cat_status_approved_idx');
            $table->index(['category_id', 'status', 'likes_count'], 'prompts_cat_status_likes_idx');
            $table->index(['model', 'status', 'approved_at'], 'prompts_model_status_approved_idx');
            $table->index(['user_id', 'status', 'approved_at'], 'prompts_user_status_approved_idx');
            $table->index(['status', 'likes_count', 'approved_at'], 'prompts_status_likes_approved_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropIndex('prompts_cat_status_approved_idx');
            $table->dropIndex('prompts_cat_status_likes_idx');
            $table->dropIndex('prompts_model_status_approved_idx');
            $table->dropIndex('prompts_user_status_approved_idx');
            $table->dropIndex('prompts_status_likes_approved_idx');
        });
    }
};
