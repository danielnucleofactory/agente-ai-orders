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
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->foreignId('kanban_status_id')->nullable()->after('status')->constrained('kanban_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_documents', function (Blueprint $table) {
            $table->dropForeign(['kanban_status_id']);
            $table->dropColumn('kanban_status_id');
        });
    }
};
