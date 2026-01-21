<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea el usuario sistema "Next Orders" para registrar cambios automáticos de Porth
     */
    public function up(): void
    {
        // Verificar si el usuario ya existe
        $exists = DB::table('users')->where('email', 'system@nextorders.app')->exists();
        
        if (!$exists) {
            DB::table('users')->insert([
                'name' => 'Next Orders',
                'email' => 'system@nextorders.app',
                'password' => Hash::make(\Illuminate\Support\Str::random(64)), // Password aleatorio no usable
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', 'system@nextorders.app')->delete();
    }
};
