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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 50)->unique()->after('id');
            $table->string('phone', 20)->nullable()->after('password');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->foreignId('unit_id')->nullable()->after('avatar')->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['nip', 'phone', 'avatar', 'unit_id', 'is_active']);
        });
    }
};
