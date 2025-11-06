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
        Schema::table('shipping_document_comments', function (Blueprint $table) {
            $table->string('action_type')->default('comment');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_document_comments', function (Blueprint $table) {
            $table->dropIndex(['action_type']);
            $table->dropColumn(['action_type', 'old_values', 'new_values', 'ip_address', 'user_agent']);
        });
    }
};
