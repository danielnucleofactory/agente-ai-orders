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
        // First, create the new authorizations table if it doesn't exist
        if (!Schema::hasTable('authorizations')) {
            Schema::create('authorizations', function (Blueprint $table) {
                $table->id();
                $table->string('operation_id')->index();
                $table->morphs('authorizable');
                $table->foreignId('requester_id')->constrained('users');
                $table->string('operation_type');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->json('data')->nullable();
                $table->foreignId('authorizer_id')->nullable()->constrained('users');
                $table->timestamp('authorized_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                // Add indexes for common queries
                $table->index(['status', 'authorizable_type', 'authorizable_id']);
                $table->index(['operation_type']);
            });
        }

        // If the old table exists, migrate the data
        if (Schema::hasTable('authorization_requests')) {
            // Copy data from the old table to the new one
            $oldRequests = DB::table('authorization_requests')->get();

            foreach ($oldRequests as $request) {
                DB::table('authorizations')->insert([
                    'id' => $request->id,
                    'operation_id' => $request->operation_id,
                    'authorizable_type' => $request->authorizable_type,
                    'authorizable_id' => $request->authorizable_id,
                    'requester_id' => $request->requester_id,
                    'operation_type' => $request->operation_type,
                    'status' => $request->status,
                    'data' => $request->data,
                    'authorizer_id' => $request->authorizer_id,
                    'authorized_at' => $request->authorized_at,
                    'notes' => $request->notes,
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ]);
            }

            // Drop the old table
            Schema::dropIfExists('authorization_requests');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create the old table back
        if (!Schema::hasTable('authorization_requests')) {
            Schema::create('authorization_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('operation_id')->index();
                $table->morphs('authorizable');
                $table->foreignId('requester_id')->constrained('users');
                $table->string('operation_type');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->json('data')->nullable();
                $table->foreignId('authorizer_id')->nullable()->constrained('users');
                $table->timestamp('authorized_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // If the new table exists, migrate the data back
        if (Schema::hasTable('authorizations')) {
            // Copy data from the new table to the old one
            DB::statement('INSERT INTO authorization_requests SELECT * FROM authorizations');

            // Drop the new table
            Schema::dropIfExists('authorizations');
        }
    }
};
