<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackingDataPO>
 */
class TrackingDataPOFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'status' => fake()->randomElement(['in_transit', 'delivered', 'delayed', 'lost']),
            'location' => fake()->city() . ', ' . fake()->country(),
            'carrier' => fake()->randomElement(['FedEx', 'UPS', 'DHL', 'USPS']),
            'tracking_number' => fake()->bothify('TRK-####-####-##??'),
            'estimated_delivery' => fake()->dateTimeBetween('now', '+30 days'),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the shipment is in transit.
     */
    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_transit',
        ]);
    }

    /**
     * Indicate that the shipment is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'estimated_delivery' => null,
        ]);
    }

    /**
     * Indicate that the shipment is delayed.
     */
    public function delayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delayed',
            'estimated_delivery' => fake()->dateTimeBetween('+30 days', '+60 days'),
        ]);
    }
}
