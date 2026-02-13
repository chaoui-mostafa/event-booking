<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->modify('+'.rand(1, 5).' days'));

        return [
            'organizer_id' => User::factory()->organizer(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => fake()->city() . ', ' . fake()->country(),
            'max_attendees' => fake()->optional()->numberBetween(50, 1000),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function forOrganizer(int $organizerId): static
    {
        return $this->state(fn (array $attributes) => [
            'organizer_id' => $organizerId,
        ]);
    }
}
