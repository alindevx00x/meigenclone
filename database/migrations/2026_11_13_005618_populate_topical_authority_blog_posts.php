<?php

use Database\Seeders\BlogSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Automatically populates the 4 new topical authority articles upon running php artisan migrate
        Artisan::call('db:seed', [
            '--class' => BlogSeeder::class,
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
