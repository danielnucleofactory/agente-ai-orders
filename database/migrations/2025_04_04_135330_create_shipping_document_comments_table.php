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
        Schema::create('shipping_document_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_document_id')->constrained('shipping_documents');
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_document_comments');
    }
};
