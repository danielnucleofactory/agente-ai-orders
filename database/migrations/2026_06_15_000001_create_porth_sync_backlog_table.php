<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('porth_sync_backlogs', function (Blueprint $table) {
            $table->id();
            $table->string('porth_id')->unique();
            $table->string('source', 50)->default('last_updated')->index();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedSmallInteger('priority')->default(100)->index();
            $table->timestamp('discovered_at')->nullable()->index();
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('porth_sync_backlogs');
    }
};
