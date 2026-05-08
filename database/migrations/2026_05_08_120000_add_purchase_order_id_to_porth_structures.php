<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addPurchaseOrderColumnIfMissing('porth_cargos');
        $this->addPurchaseOrderColumnIfMissing('porth_phases');
        $this->addPurchaseOrderColumnIfMissing('porth_itineraries');
    }

    public function down(): void
    {
        $this->dropPurchaseOrderColumnIfExists('porth_itineraries');
        $this->dropPurchaseOrderColumnIfExists('porth_phases');
        $this->dropPurchaseOrderColumnIfExists('porth_cargos');
    }

    private function addPurchaseOrderColumnIfMissing(string $table): void
    {
        if (!Schema::hasTable($table) || Schema::hasColumn($table, 'purchase_order_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->foreignId('purchase_order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->after('shipping_document_id');
        });
    }

    private function dropPurchaseOrderColumnIfExists(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'purchase_order_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->dropConstrainedForeignId('purchase_order_id');
        });
    }
};
