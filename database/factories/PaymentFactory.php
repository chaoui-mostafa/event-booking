<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        // ضمان booking موجودة و مملوءة
        $booking = Booking::inRandomOrder()->first() ?? Booking::factory()->create();

        return [
            'payment_reference' => 'PAY-' . strtoupper(uniqid()),
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'payment_details' => [
                'transaction_id' => fake()->uuid(),
                'last_four' => fake()->optional()->numerify('####'),
                'card_type' => fake()->optional()->randomElement(['visa', 'mastercard', 'amex']),
            ],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'payment_details' => [
                'error' => fake()->sentence(),
                'error_code' => fake()->numerify('ERR-####'),
            ],
        ]);
    }
}
