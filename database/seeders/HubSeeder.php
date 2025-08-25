<?php

namespace Database\Seeders;

use App\Models\Hub;
use Illuminate\Database\Seeder;

class HubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hubs = [
            [
                'name' => 'Miami Hub',
                'code' => 'MIA',
                'country' => 'Estados Unidos'
            ],
            [
                'name' => 'Shanghai Hub',
                'code' => 'SHA',
                'country' => 'China'
            ],
            [
                'name' => 'Rotterdam Hub',
                'code' => 'RTM',
                'country' => 'Países Bajos'
            ],
            [
                'name' => 'Dubai Hub',
                'code' => 'DXB',
                'country' => 'Emiratos Árabes Unidos'
            ],
            [
                'name' => 'Singapore Hub',
                'code' => 'SIN',
                'country' => 'Singapur'
            ],
        ];

        foreach ($hubs as $hub) {
            Hub::create($hub);
        }
    }
}
