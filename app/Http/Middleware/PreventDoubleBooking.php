<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PreventDoubleBooking
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $eventId = $request->route('event') ?? $request->event_id;
        $ticketId = $request->ticket_id;

        if (!$eventId || !$ticketId) {
            return $next($request);
        }

        // Check for existing confirmed booking
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active booking for this ticket',
                'data' => [
                    'booking_reference' => $existingBooking->booking_reference,
                    'status' => $existingBooking->status,
                ]
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
