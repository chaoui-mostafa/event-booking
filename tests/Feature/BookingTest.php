<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $organizer;
    protected $event;
    protected $ticket;
    protected $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->organizer = User::factory()->organizer()->create();
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published'
        ]);
        $this->ticket = Ticket::factory()->create([
            'event_id' => $this->event->id,
            'price' => 100.00,
            'quantity' => 50,
            'available_quantity' => 50
        ]);
        $this->customerToken = $this->customer->createToken('test-token')->plainTextToken;
    }

    public function test_customer_can_book_tickets()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->customerToken)
            ->postJson('/api/v1/events/' . $this->event->id . '/book', [
                'ticket_id' => $this->ticket->id,
                'quantity' => 2
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'booking_reference',
                    'quantity',
                    'total_amount',
                    'status',
                    'event',
                    'ticket'
                ]
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->customer->id,
            'event_id' => $this->event->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 2,
            'total_amount' => 200.00,
            'status' => 'pending'
        ]);

        $this->ticket->refresh();
        $this->assertEquals(48, $this->ticket->available_quantity);
    }
}
