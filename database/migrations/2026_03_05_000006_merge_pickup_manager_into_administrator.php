<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Promote legacy pickup managers to administrators.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'pickup_manager')
            ->update(['role' => 'administrator']);
    }

    /**
     * No safe rollback: original pickup_manager rows cannot be reliably identified.
     */
    public function down(): void
    {
        // Intentionally left blank.
    }
};
