<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Event Reminder: ' . $this->booking->event->title . ' Tomorrow!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that your event is tomorrow!')
            ->line('Event: ' . $this->booking->event->title)
            ->line('Date: ' . $this->booking->event->start_date->format('F j, Y g:i A'))
            ->line('Location: ' . $this->booking->event->location)
            ->line('Booking Reference: ' . $this->booking->booking_reference)
            ->line('Please arrive 15 minutes early for check-in.')
            ->action('View Event Details', url('/api/v1/events/' . $this->booking->event->id))
            ->line('We look forward to seeing you there!');
    }

    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'event_title' => $this->booking->event->title,
            'event_date' => $this->booking->event->start_date->toISOString(),
            'message' => 'Reminder: ' . $this->booking->event->title . ' is tomorrow!',
        ];
    }
}
