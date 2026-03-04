<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table): void {

            $table->uuid('id')->primary();

            $table->foreignUuid('ticket_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('body');

            $table->boolean('is_internal')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};