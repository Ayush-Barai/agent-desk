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
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->nullableUuidMorphs('auditable');
            $table->string('action');
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index('actor_user_id');
            $table->index('ticket_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
