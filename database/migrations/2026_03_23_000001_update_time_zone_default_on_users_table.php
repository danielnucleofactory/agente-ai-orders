<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing legacy keys to IANA timezone strings
        DB::table('users')->where('time_zone', 'op2')->update(['time_zone' => 'America/Santiago']);
        DB::table('users')->where('time_zone', 'op1')->update(['time_zone' => 'America/Costa_Rica']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('time_zone')->default('America/Santiago')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('time_zone', 'America/Santiago')->update(['time_zone' => 'op2']);
        DB::table('users')->where('time_zone', 'America/Costa_Rica')->update(['time_zone' => 'op1']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('time_zone')->default('op2')->change();
        });
    }
};
