<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Cancelled - ' . $this->booking->event->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been cancelled.')
            ->line('Booking Reference: **' . $this->booking->booking_reference . '**')
            ->line('Event: ' . $this->booking->event->title)
            ->line('If you did not request this cancellation, please contact support.')
            ->action('View Details', url('/api/v1/bookings/' . $this->booking->id))
            ->line('Thank you for using our event booking system!');
    }

    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_reference' => $this->booking->booking_reference,
            'event_title' => $this->booking->event->title,
            'message' => 'Your booking for ' . $this->booking->event->title . ' has been cancelled.',
        ];
    }
}
