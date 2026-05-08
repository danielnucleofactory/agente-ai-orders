<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropNotNullConstraintIfPresent('porth_cargos', 'shipping_document_id');
        $this->dropNotNullConstraintIfPresent('porth_phases', 'shipping_document_id');
        $this->dropNotNullConstraintIfPresent('porth_itineraries', 'shipping_document_id');
    }

    public function down(): void
    {
        $this->setNotNullConstraintIfSafe('porth_itineraries', 'shipping_document_id');
        $this->setNotNullConstraintIfSafe('porth_phases', 'shipping_document_id');
        $this->setNotNullConstraintIfSafe('porth_cargos', 'shipping_document_id');
    }

    private function dropNotNullConstraintIfPresent(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ALTER COLUMN %s DROP NOT NULL',
            $table,
            $column
        ));
    }

    private function setNotNullConstraintIfSafe(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $nullCount = DB::table($table)->whereNull($column)->count();

        if ($nullCount > 0) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ALTER COLUMN %s SET NOT NULL',
            $table,
            $column
        ));
    }
};
