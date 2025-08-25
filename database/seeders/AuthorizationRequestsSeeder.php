<?php

namespace Database\Seeders;

use App\Models\AuthorizationRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthorizationRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el único usuario disponible en el sistema
        $user = User::first();

        if (!$user) {
            $this->command->error('No hay usuarios en el sistema. Debes crear al menos un usuario.');
            return;
        }

        // Operaciones posibles en el sistema
        $operations = [
            'moveTask' => [
                'type' => 'App\Models\Task',
                'data' => fn() => [
                    'current_status' => fake()->randomElement(['pending', 'in_progress', 'testing']),
                    'new_status' => fake()->randomElement(['in_progress', 'testing', 'completed']),
                    'priority' => fake()->randomElement(['low', 'medium', 'high']),
                ]
            ],
            'attachDocumentToPO' => [
                'type' => 'App\Models\PurchaseOrder',
                'data' => fn() => [
                    'document_type' => fake()->randomElement(['invoice', 'packing_list', 'bill_of_lading', 'certificate']),
                    'file_name' => fake()->randomElement(['invoice-123.pdf', 'packing-234.pdf', 'bl-567.pdf', 'cert-789.pdf']),
                    'file_size' => fake()->numberBetween(100000, 5000000),
                ]
            ],
            'addComment' => [
                'type' => 'App\Models\Comment',
                'data' => fn() => [
                    'content' => fake()->paragraph(),
                    'has_attachment' => fake()->boolean(70),
                ]
            ],
            'changeSupplier' => [
                'type' => 'App\Models\Supplier',
                'data' => fn() => [
                    'old_supplier_id' => fake()->numberBetween(1, 100),
                    'new_supplier_id' => fake()->numberBetween(1, 100),
                    'reason' => fake()->sentence(),
                ]
            ],
            'approveShipment' => [
                'type' => 'App\Models\Shipment',
                'data' => fn() => [
                    'tracking_number' => 'TRK-' . fake()->numerify('#####'),
                    'carrier' => fake()->randomElement(['DHL', 'FedEx', 'UPS', 'USPS']),
                    'destination' => fake()->country(),
                    'estimated_arrival' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
                ]
            ]
        ];

        // Crear solicitudes con estados variados
        for ($i = 0; $i < 20; $i++) {
            $operationKey = fake()->randomElement(array_keys($operations));
            $operationDetails = $operations[$operationKey];
            $status = fake()->randomElement(['pending', 'approved', 'rejected']);
            $created = fake()->dateTimeBetween('-30 days', 'now');

            $attributes = [
                'operation_id' => Str::uuid(),
                'authorizable_type' => $operationDetails['type'],
                'authorizable_id' => fake()->numberBetween(1, 50),
                'requester_id' => $user->id,
                'operation_type' => $operationKey,
                'status' => $status,
                'data' => $operationDetails['data'](),
                'created_at' => $created,
                'updated_at' => $created,
            ];

            // Si está aprobado o rechazado, agregar quién lo autorizó
            if ($status !== 'pending') {
                $attributes['authorizer_id'] = $user->id;
                $attributes['authorized_at'] = Carbon::parse($created)->addHours(fake()->numberBetween(1, 48));
                $attributes['updated_at'] = $attributes['authorized_at'];
                $attributes['notes'] = fake()->sentence();
            }

            AuthorizationRequest::create($attributes);
        }

        // Crear algunas solicitudes recientes pendientes (para pruebas de aprobación)
        for ($i = 0; $i < 10; $i++) {
            $operationKey = fake()->randomElement(array_keys($operations));
            $operationDetails = $operations[$operationKey];
            $created = fake()->dateTimeBetween('-2 days', 'now');

            $attributes = [
                'operation_id' => Str::uuid(),
                'authorizable_type' => $operationDetails['type'],
                'authorizable_id' => fake()->numberBetween(1, 50),
                'requester_id' => $user->id,
                'operation_type' => $operationKey,
                'status' => 'pending',
                'data' => $operationDetails['data'](),
                'created_at' => $created,
                'updated_at' => $created,
            ];

            AuthorizationRequest::create($attributes);
        }

        $this->command->info('Se han creado 30 solicitudes de autorización de ejemplo.');
    }
}
