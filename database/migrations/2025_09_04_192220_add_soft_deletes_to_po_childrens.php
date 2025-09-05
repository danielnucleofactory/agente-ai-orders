<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function addSoftDeletesIfMissing(string $table): void
    {
        if (!Schema::hasColumn($table, 'deleted_at')) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();      // agrega 'deleted_at' nullable
                $table->index('deleted_at'); // index para filtros
            });
        }
    }

    public function up(): void
    {
        // Tablas relacionadas a Purchase Orders
        $this->addSoftDeletesIfMissing('purchase_order_comments');
        $this->addSoftDeletesIfMissing('tracking_data_pos');
        $this->addSoftDeletesIfMissing('boarding_documents');
        $this->addSoftDeletesIfMissing('purchase_order_product');
        $this->addSoftDeletesIfMissing('purchase_order_shipping_document');
    }

    public function down(): void
    {
        foreach ([
                     'purchase_order_comments',
                     'tracking_data_pos',
                     'boarding_documents',
                     'purchase_order_product',
                     'purchase_order_shipping_document',
                 ] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropIndex(['deleted_at']);
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
