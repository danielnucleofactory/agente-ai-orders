<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Desactivar temporalmente las restricciones de clave foránea
            Schema::disableForeignKeyConstraints();

            // Note: Basic users are already created in CompanySeeder
            // Here we can create additional users if needed

            // Get all companies to create users for them
            $companies = Company::all();

            // Create 3 users for each company
            foreach ($companies as $company) {
                User::factory()
                    ->count(3)
                    ->create([
                        'company_id' => $company->id
                    ]);
            }

            // Create a main admin user for the main company
            $mainCompany = Company::where('name', 'Main Test Company')->first();
            if ($mainCompany) {
                User::factory()->create([
                    'company_id' => $mainCompany->id,
                    'name' => 'Admin User',
                    'email' => 'admin@test.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
            }

            // Create some verified users
            User::factory()
                ->count(5)
                ->create();

            // Create some unverified users
            User::factory()
                ->unverified()
                ->count(3)
                ->create();

            // Create a test user
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
