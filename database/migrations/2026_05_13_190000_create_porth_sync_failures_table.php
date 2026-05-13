<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('porth_sync_failures', function (Blueprint $table) {
            $table->id();
            $table->string('failure_key')->unique();
            $table->string('failure_source', 50)->index();
            $table->string('job_class')->nullable();
            $table->string('document_type')->nullable()->index();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->nullable()->index();
            $table->string('container_number')->nullable()->index();
            $table->string('porth_id')->nullable()->index();
            $table->string('status', 50)->default('pending_retry')->index();
            $table->unsignedInteger('queue_attempts')->default(0);
            $table->unsignedInteger('retry_attempts')->default(0);
            $table->unsignedInteger('max_retry_attempts')->default(5);
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->timestamp('first_failed_at')->nullable()->index();
            $table->timestamp('last_failed_at')->nullable()->index();
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('recovered_at')->nullable()->index();
            $table->text('last_retry_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('porth_sync_failures');
    }
};
