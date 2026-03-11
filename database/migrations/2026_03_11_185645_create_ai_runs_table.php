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
        Schema::create('ai_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignUuid('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('run_type', 20);
            $table->string('status', 20)->default('queued');
            $table->string('input_hash', 64);
            $table->json('input_json')->nullable();
            $table->json('output_json')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->text('error_message')->nullable();
            $table->string('progress_state')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('ticket_id');
            $table->index('initiated_by_user_id');
            $table->index('run_type');
            $table->index('status');
            $table->index('input_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_runs');
    }
};
