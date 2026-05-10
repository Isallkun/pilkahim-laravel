<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'password' => static::$password ??= Hash::make('password'),
            'angkatan' => $this->faker->randomElement([
                'Panjer Pambayung',
                'Bubuhan Danadyaksa',
                'Arjuna Pangarsa',
            ]),
            'gender' => $this->faker->randomElement(['L', 'P']),
            'has_voted' => false,
            'password_changed_at' => null,
        ];
    }

    /**
     * User yang sudah ganti password.
     */
    public function passwordChanged(): static
    {
        return $this->state(fn (array $attributes) => [
            'password_changed_at' => now(),
        ]);
    }

    /**
     * User yang sudah voting.
     */
    public function voted(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_voted' => true,
        ]);
    }
}
