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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->string('title', 200);
            $table->text('description');

            // Foreign Key - User relations
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_priority_id')->constrained('priorities');
            $table->foreignId('final_priority_id')->nullable()->constrained('priorities');

            // Status
            $table->enum('status', [
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
            ])->default('new');

            // Assignment
            $table->foreignId('assigned_to_id')->nullable()->constrained('users');
            $table->foreignId('assigned_by_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();

            //Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            //Resolution & Pending
            $table->text('resolution')->nullable();
            $table->text('pending_reason')->nullable();
            $table->enum('pending_type', ['user', 'external'])->nullable();
            $table->text('return_reason')->nullable();

            //Auto-close & Reopen
            $table->timestamp('auto_close_at')->nullable();
            $table->integer('reopen_count')->default(0);

            $table->timestamps();

            //Indexes untuk performa
            $table->index('status');
            $table->index('reporter_id');
            $table->index('assigned_to_id');
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
