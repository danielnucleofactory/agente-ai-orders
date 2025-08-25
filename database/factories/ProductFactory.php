<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 500);
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $netValue = $price * $quantity;
        $vatRate = 16.00;
        $vatValue = $netValue * ($vatRate / 100);

        return [
            'material_id' => 'MAT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraph(),
            'legacy_material' => $this->faker->optional(0.7)->bothify('LEG-####'),
            'contract' => $this->faker->optional(0.8)->bothify('CONT-####-##'),
            'order_quantity' => $quantity,
            'qty_unit' => $this->faker->randomElement(['pcs', 'kg', 'lt', 'm']),
            'price_per_unit' => $price,
            'price_per_uon' => $price,
            'net_value' => $netValue,
            'vat_rate' => $vatRate,
            'vat_value' => $vatValue,
            'delivery_date' => $this->faker->dateTimeBetween('now', '+3 months'),
        ];
    }
}
