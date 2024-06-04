<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\SunrayEu\ProductDescriptionAnalyser\App\Models\Product>
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
        $generatedDescription = fake()->text();

        return [
            'name' => fake()->name(),
            'description' => $generatedDescription,
            'score' => fake()->numberBetween(-1, 1),
            'hash' => md5($generatedDescription),
        ];
    }
}
