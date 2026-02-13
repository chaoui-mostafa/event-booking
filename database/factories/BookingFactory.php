<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        // ضمان ticket موجودة
        $ticket = Ticket::inRandomOrder()->first() ?? Ticket::factory()->create();

        $quantity = fake()->numberBetween(1, 5);

        return [
            'booking_reference' => 'BK-' . strtoupper(uniqid()),
            'user_id' => User::factory()->create()->id, // create user مباشرة
            'event_id' => $ticket->event_id ?? Event::factory()->create()->id,
            'ticket_id' => $ticket->id,
            'quantity' => $quantity, // ضروري باش يتجاوز ال NOT NULL
            'total_amount' => $ticket->price * $quantity,
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
