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
        Schema::create('tickets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_id')->constrained('users');
            $table->foreignUuid('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->text('summary')->nullable();
            $table->string('status', 20)->default('new');
            $table->string('priority', 20)->nullable();
            $table->boolean('escalation_required')->default(false);
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('triaged_at')->nullable();
            $table->timestamp('last_requester_message_at')->nullable();
            $table->timestamp('last_agent_message_at')->nullable();
            $table->timestamps();

            $table->index('requester_id');
            $table->index('assigned_to_user_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('priority');
            $table->index(['status', 'assigned_to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
