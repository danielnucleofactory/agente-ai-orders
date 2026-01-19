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
        Schema::create('porth_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 50)->default('manual')->index(); // cron, schedule, manual, webhook
            $table->string('scope')->nullable(); // ej: "recent:2h"
            $table->string('status', 50)->default('running')->index(); // running, success, partial, failed
            $table->timestamp('started_at')->useCurrent()->index();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('no_change')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('dry_run')->default(0);
            $table->text('error_summary')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('porth_sync_runs');
    }
};
