<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Election>
 */
class ElectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Pemilihan Ketua Umum ' . $this->faker->year(),
            'start_date' => now(),
            'end_date' => now()->addHours(8),
            'status' => 'draft',
            'result_visibility' => 'private',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHours(7),
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
            'start_date' => now()->subHours(9),
            'end_date' => now()->subHour(),
        ]);
    }

    public function publicResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'result_visibility' => 'public',
        ]);
    }
}
