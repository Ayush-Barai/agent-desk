<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {

            $table->uuid('id')->primary();

            $table->string('title');
            $table->text('description');

            $table->string('status')
                ->default('open');

            $table->string('priority')
                ->default('medium');

            // Requester
            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Assigned agent (nullable)
            $table->foreignUuid('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};