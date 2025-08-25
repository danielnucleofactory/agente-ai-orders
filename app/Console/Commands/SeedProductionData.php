<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\ProductionSeeder;
use Illuminate\Console\Command;

class SeedProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-production
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with production data (without using Faker)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->laravel->environment('production') && !$this->option('force')) {
            $this->error('This command is executing in production environment.');
            $this->error('Use the --force flag to execute this command in production.');
            return 1;
        }

        $this->info('Seeding production data...');

        $this->components->task('Running production seeder', function () {
            $seeder = $this->laravel->make(ProductionSeeder::class);
            $seeder->run();
            return true;
        });

        $this->newLine();
        $this->info('Production data seeded successfully!');

        return 0;
    }
}
