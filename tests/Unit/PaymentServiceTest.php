<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Event;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    public function test_it_processes_payment_successfully()
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'price' => 100.00,
            'quantity' => 50,
            'available_quantity' => 50
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200.00,
            'status' => 'pending',
            'booking_reference' => 'BK-' . uniqid()
        ]);

        $result = $this->paymentService->processPayment(
            $booking,
            'credit_card',
            ['card_number' => '4111111111111111']
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('Payment processed successfully', $result['message']);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 200.00,
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed'
        ]);

        $ticket->refresh();
        $this->assertEquals(48, $ticket->available_quantity);
    }

    public function test_it_handles_payment_failure()
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'price' => 100.00,
            'quantity' => 50,
            'available_quantity' => 50
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200.00,
            'status' => 'pending',
            'booking_reference' => 'BK-' . uniqid()
        ]);

        $result = $this->paymentService->processPayment($booking, 'credit_card', []);

        $this->assertIsArray($result);
    }

    public function test_it_processes_refund_successfully()
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'total_amount' => 200.00,
            'status' => 'confirmed',
            'booking_reference' => 'BK-' . uniqid(),
            'quantity' => 1

        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 200.00,
            'payment_method' => 'credit_card',
            'status' => 'completed',
            'payment_reference' => 'PAY-' . uniqid()
        ]);

        $result = $this->paymentService->refundPayment($payment);

        $this->assertIsArray($result);
    }

    public function test_it_cannot_refund_pending_payment()
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'total_amount' => 100.00,
            'status' => 'confirmed',
            'booking_reference' => 'BK-' . uniqid(),
            'quantity' => 1
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'payment_method' => 'credit_card',
            'status' => 'pending',
            'payment_reference' => 'PAY-' . uniqid()
        ]);

        $result = $this->paymentService->refundPayment($payment);

        $this->assertFalse($result['success']);
        $this->assertEquals('Only completed payments can be refunded', $result['message']);
    }
}
