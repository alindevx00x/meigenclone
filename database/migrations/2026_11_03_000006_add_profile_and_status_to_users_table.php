<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('role');
            $table->text('bio')->nullable()->after('status');
            $table->string('website')->nullable()->after('bio');
            $table->string('avatar_url')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'bio', 'website', 'avatar_url']);
        });
    }
};
