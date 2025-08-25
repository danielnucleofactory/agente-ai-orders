<?php

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
        Schema::create('authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->string('operation_id')->unique();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorization_requests');
    }
};
