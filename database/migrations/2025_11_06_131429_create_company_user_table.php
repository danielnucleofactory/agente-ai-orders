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
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        // Migrar datos existentes de company_id en users a la tabla pivot
        $this->migrateExistingCompanyData();
    }

    /**
     * Migrar datos existentes de company_id a la tabla pivot
     */
    private function migrateExistingCompanyData(): void
    {
        // Obtener usuarios que tienen company_id asignado
        $usersWithCompany = DB::table('users')
            ->whereNotNull('company_id')
            ->select('id', 'company_id', 'created_at', 'updated_at')
            ->get();

        // Crear registros en la tabla pivot
        foreach ($usersWithCompany as $user) {
            DB::table('company_user')->insert([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'created_at' => $user->created_at ?? now(),
                'updated_at' => $user->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
