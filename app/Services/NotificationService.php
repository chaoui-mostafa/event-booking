<?php

namespace App\Services;

use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\EventReminderNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send booking confirmation notification
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        try {
            $booking->user->notify(new BookingConfirmedNotification($booking));

            Log::info('Booking confirmation notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send booking cancellation notification
     */
    public function sendBookingCancellation(Booking $booking): void
    {
        try {
            $booking->user->notify(new BookingCancelledNotification($booking));

            Log::info('Booking cancellation notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking cancellation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send event reminder notifications (for cron job)
     */
    public function sendEventReminders(): void
    {
        $tomorrow = now()->addDay()->startOfDay();
        $dayAfterTomorrow = now()->addDay()->endOfDay();

        $bookings = Booking::with(['user', 'event'])
            ->where('status', 'confirmed')
            ->whereHas('event', function ($query) use ($tomorrow, $dayAfterTomorrow) {
                $query->whereBetween('start_date', [$tomorrow, $dayAfterTomorrow]);
            })
            ->get();

        foreach ($bookings as $booking) {
            try {
                $booking->user->notify(new EventReminderNotification($booking));

                Log::info('Event reminder sent', [
                    'booking_id' => $booking->id,
                    'event_id' => $booking->event_id,
                    'user_id' => $booking->user_id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send event reminder', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send bulk notifications (for organizers)
     */
    public function sendBulkNotification(int $eventId, string $subject, string $message): void
    {
        $bookings = Booking::with('user')
            ->where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->get();

        foreach ($bookings as $booking) {
            // Queue custom notification here
            // You can create a CustomNotification class for this
        }
    }
}
