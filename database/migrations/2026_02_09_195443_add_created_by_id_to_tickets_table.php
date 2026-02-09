<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add created_by_id field to track WHO CREATED the ticket.
     * This allows separating the ticket creator from the reporter.
     * For example: Helpdesk creates ticket on behalf of someone else.
     */
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('tickets', 'created_by_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('created_by_id')
                    ->nullable()
                    ->after('reporter_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        // Set created_by_id = reporter_id for existing tickets where it's null
        DB::statement('UPDATE tickets SET created_by_id = reporter_id WHERE created_by_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'created_by_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['created_by_id']);
                $table->dropColumn('created_by_id');
            });
        }
    }
};
