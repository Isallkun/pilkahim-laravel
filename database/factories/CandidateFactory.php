<?php

namespace Database\Factories;

use App\Models\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'name' => $this->faker->name(),
            'photo_path' => null,
            'visi' => $this->faker->paragraph(3),
            'misi' => $this->faker->paragraph(5),
            'video_url' => null,
            'sort_order' => 0,
        ];
    }
}
