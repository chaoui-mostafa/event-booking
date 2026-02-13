<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public event routes
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/events/{event}/tickets', [TicketController::class, 'index']);
    Route::get('/events/{event}/tickets/available', [TicketController::class, 'available']);
    Route::get('/events/{event}/tickets/{ticket}', [TicketController::class, 'show']);

    // Public booking lookup
    Route::get('/bookings/lookup', [BookingController::class, 'lookup']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Booking routes
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::post('/bookings/{booking}/pay', [BookingController::class, 'processPayment']);

        // Create booking (with double booking prevention)
        Route::post('/events/{event}/book', [BookingController::class, 'store'])
            ->middleware('prevent.double.booking');

        // Organizer/Admin only routes
        Route::middleware('role:admin,organizer')->group(function () {
            // Event management
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{event}', [EventController::class, 'update']);
            Route::delete('/events/{event}', [EventController::class, 'destroy']);
            Route::patch('/events/{event}/publish', [EventController::class, 'publish']);
            Route::patch('/events/{event}/cancel', [EventController::class, 'cancel']);
            Route::get('/my/events', [EventController::class, 'myEvents']);

            // Ticket management
            Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
            Route::put('/events/{event}/tickets/{ticket}', [TicketController::class, 'update']);
            Route::delete('/events/{event}/tickets/{ticket}', [TicketController::class, 'destroy']);
        });
    });
});
