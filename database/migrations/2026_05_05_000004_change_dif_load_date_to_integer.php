<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_orders', 'dif_load_date')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchase_orders ALTER COLUMN dif_load_date DROP DEFAULT');
            DB::statement('ALTER TABLE purchase_orders ALTER COLUMN dif_load_date TYPE integer USING NULL');
            DB::statement("
                UPDATE purchase_orders
                SET dif_load_date = CAST(DATE_PART('day', date_variable_date::timestamp - date_theorical_load::timestamp) AS integer)
                WHERE date_theorical_load IS NOT NULL
                  AND date_variable_date IS NOT NULL
            ");

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE purchase_orders MODIFY dif_load_date INT NULL');
            DB::statement("
                UPDATE purchase_orders
                SET dif_load_date = TIMESTAMPDIFF(DAY, DATE(date_theorical_load), DATE(date_variable_date))
                WHERE date_theorical_load IS NOT NULL
                  AND date_variable_date IS NOT NULL
            ");

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement("
                UPDATE purchase_orders
                SET dif_load_date = CAST(julianday(date_variable_date) - julianday(date_theorical_load) AS INTEGER)
                WHERE date_theorical_load IS NOT NULL
                  AND date_variable_date IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('purchase_orders', 'dif_load_date')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchase_orders ALTER COLUMN dif_load_date TYPE timestamp(0) without time zone USING NULL');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE purchase_orders MODIFY dif_load_date TIMESTAMP NULL');
        }
    }
};
