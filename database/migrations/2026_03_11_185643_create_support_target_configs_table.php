<?php

declare(strict_types=1);

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
        Schema::create('support_target_configs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->integer('first_response_hours')->default(24);
            $table->integer('resolution_hours')->default(72);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_target_configs');
    }
};
