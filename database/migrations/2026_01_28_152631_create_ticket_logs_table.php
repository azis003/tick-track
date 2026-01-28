<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 50);
            $table->enum('from_status', [
                'new',
                'triage',
                'assigned',
                'in_progress',
                'pending_user',
                'pending_external',
                'waiting_approval',
                'resolved',
                'closed',
                'reopened'
            ])->nullable();
            $table->enum('to_status', [
                'new',
                'triage',
                'assigned',
                'in_progress',
                'pending_user',
                'pending_external',
                'waiting_approval',
                'resolved',
                'closed',
                'reopened'
            ]);
            $table->text('notes')->nullable();
            $table->timestamp('created_at');

            $table->index('ticket_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};
