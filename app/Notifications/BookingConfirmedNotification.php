<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed - ' . $this->booking->event->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed successfully.')
            ->line('Booking Reference: **' . $this->booking->booking_reference . '**')
            ->line('Event: ' . $this->booking->event->title)
            ->line('Date: ' . $this->booking->event->start_date->format('F j, Y g:i A'))
            ->line('Location: ' . $this->booking->event->location)
            ->line('Ticket: ' . $this->booking->ticket->name)
            ->line('Quantity: ' . $this->booking->quantity)
            ->line('Total Amount: $' . number_format($this->booking->total_amount, 2))
            ->action('View Booking', url('/api/v1/bookings/' . $this->booking->id))
            ->line('Thank you for using our event booking system!');
    }

    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_reference' => $this->booking->booking_reference,
            'event_title' => $this->booking->event->title,
            'event_date' => $this->booking->event->start_date->toISOString(),
            'ticket_name' => $this->booking->ticket->name,
            'quantity' => $this->booking->quantity,
            'total_amount' => $this->booking->total_amount,
            'message' => 'Your booking for ' . $this->booking->event->title . ' has been confirmed!',
        ];
    }
}
