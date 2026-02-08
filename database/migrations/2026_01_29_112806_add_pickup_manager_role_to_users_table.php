<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up any existing temporary table
        DB::statement('DROP TABLE IF EXISTS users_new');
        
        // SQLite doesn't support modifying columns directly, so we need to recreate the table
        DB::statement('CREATE TABLE users_new AS SELECT * FROM users');
        
        // Drop the old table
        DB::statement('DROP TABLE users');
        
        // Create the new table with updated structure
        DB::statement('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "customer" CHECK (role IN ("administrator", "pickup_manager", "vendor", "customer")),
            is_active INTEGER NOT NULL DEFAULT 1,
            email_verified_at TIMESTAMP NULL,
            remember_token TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            profile_picture TEXT NULL,
            bio TEXT NULL
        )');
        
        // Copy data back
        DB::statement('INSERT INTO users (id, email, password, role, is_active, email_verified_at, remember_token, created_at, updated_at, profile_picture, bio)
                       SELECT id, email, password, 
                              CASE 
                                  WHEN role = "vendor" THEN "vendor"
                                  WHEN role = "administrator" THEN "administrator" 
                                  ELSE "customer"
                              END as role,
                              is_active, email_verified_at, remember_token, created_at, updated_at,
                              profile_picture, bio
                       FROM users_new');
        
        // Drop the temporary table
        DB::statement('DROP TABLE users_new');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clean up any existing temporary table
        DB::statement('DROP TABLE IF EXISTS users_new');
        
        // SQLite doesn't support modifying columns directly, so we need to recreate the table
        DB::statement('CREATE TABLE users_new AS SELECT * FROM users');
        
        // Drop the old table
        DB::statement('DROP TABLE users');
        
        // Create the new table with original structure
        DB::statement('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "customer" CHECK (role IN ("administrator", "vendor", "customer")),
            is_active INTEGER NOT NULL DEFAULT 1,
            email_verified_at TIMESTAMP NULL,
            remember_token TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            profile_picture TEXT NULL,
            bio TEXT NULL
        )');
        
        // Copy data back, converting pickup_manager to customer
        DB::statement('INSERT INTO users (id, email, password, role, is_active, email_verified_at, remember_token, created_at, updated_at, profile_picture, bio)
                       SELECT id, email, password, 
                              CASE 
                                  WHEN role = "pickup_manager" THEN "customer"
                                  ELSE role
                              END as role,
                              is_active, email_verified_at, remember_token, created_at, updated_at,
                              profile_picture, bio
                       FROM users_new');
        
        // Drop the temporary table
        DB::statement('DROP TABLE users_new');
    }
};
