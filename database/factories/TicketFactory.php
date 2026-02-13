<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 500);

        return [
            'event_id' => Event::factory(),
            'name' => fake()->words(3, true) . ' Ticket',
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 500),
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'sales_end_at' => fake()->optional()->dateTimeBetween('now', '+2 months'),
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_quantity' => 0,
        ]);
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'available_quantity' => $quantity,
        ]);
    }

    public function forEvent(int $eventId): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $eventId,
        ]);
    }
}
