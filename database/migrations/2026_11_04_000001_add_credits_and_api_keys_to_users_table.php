<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('credits')->default(25)->after('role');
            $table->text('openai_api_key')->nullable()->after('website');
            $table->text('replicate_api_token')->nullable()->after('openai_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['credits', 'openai_api_key', 'replicate_api_token']);
        });
    }
};
