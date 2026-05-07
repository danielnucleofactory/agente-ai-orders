<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'tracking_not_applicable')) {
                $table->boolean('tracking_not_applicable')->default(false);
            }

            if (! Schema::hasColumn('purchase_orders', 'tracking_not_applicable_reason')) {
                $table->text('tracking_not_applicable_reason')->nullable();
            }

            if (! Schema::hasColumn('purchase_orders', 'tracking_not_applicable_approved_by')) {
                $table->foreignId('tracking_not_applicable_approved_by')->nullable()->constrained('users');
            }

            if (! Schema::hasColumn('purchase_orders', 'tracking_not_applicable_approved_at')) {
                $table->timestamp('tracking_not_applicable_approved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'tracking_not_applicable_approved_by')) {
                $table->dropConstrainedForeignId('tracking_not_applicable_approved_by');
            }

            foreach ([
                'tracking_not_applicable_approved_at',
                'tracking_not_applicable_reason',
                'tracking_not_applicable',
            ] as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
