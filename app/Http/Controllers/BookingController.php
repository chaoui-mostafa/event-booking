<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    protected $paymentService;
    protected $notificationService;

    public function __construct(
        PaymentService $paymentService,
        NotificationService $notificationService
    ) {
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's bookings
     */
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['event', 'ticket', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse([
            'bookings' => BookingResource::collection($bookings),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ]
        ], 'Bookings retrieved successfully');
    }

    /**
     * Create a new booking
     */
    public function store(CreateBookingRequest $request, Event $event)
    {
        try {
            DB::beginTransaction();

            $ticket = Ticket::findOrFail($request->ticket_id);

            // Calculate total amount
            $totalAmount = $ticket->price * $request->quantity;

            // Create booking
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'event_id' => $event->id,
                'ticket_id' => $ticket->id,
                'quantity' => $request->quantity,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Temporarily reduce available quantity
            $ticket->decrement('available_quantity', $request->quantity);

            DB::commit();

            return $this->successResponse(
                new BookingResource($booking->load(['event', 'ticket'])),
                'Booking created successfully. Proceed to payment.',
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to create booking: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get booking details
     */
    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        return $this->successResponse(
            new BookingResource($booking->load(['event', 'ticket', 'payment'])),
            'Booking retrieved successfully'
        );
    }

    /**
     * Process payment for booking
     */
    public function processPayment(ProcessPaymentRequest $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if ($booking->status !== 'pending') {
            return $this->errorResponse(
                'Booking cannot be processed. Current status: ' . $booking->status,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($booking->created_at->diffInMinutes(now()) > 15) {
            $booking->update(['status' => 'expired']);
            $booking->ticket->increment('available_quantity', $booking->quantity);

            return $this->errorResponse(
                'Booking expired. Please create a new booking.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $paymentDetails = $request->only(['payment_method', 'card_number', 'card_expiry', 'card_cvv', 'paypal_email']);
        $result = $this->paymentService->processPayment($booking, $request->payment_method, $paymentDetails);

        if ($result['success']) {
            $this->notificationService->sendBookingConfirmation($booking);

            return $this->successResponse([
                'booking' => new BookingResource($result['booking']),
                'payment' => new PaymentResource($result['payment']),
            ], 'Payment successful! Booking confirmed.');
        }

        return $this->errorResponse(
            $result['message'],
            Response::HTTP_PAYMENT_REQUIRED,
            ['payment_details' => $result['payment'] ?? null]
        );
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return $this->errorResponse(
                'Booking cannot be cancelled. Current status: ' . $booking->status,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            DB::beginTransaction();

            if ($booking->status === 'confirmed' && $booking->payment) {
                $refundResult = $this->paymentService->refundPayment($booking->payment);

                if (!$refundResult['success']) {
                    throw new \Exception('Refund failed');
                }
            }

            if ($booking->status === 'pending') {
                $booking->ticket->increment('available_quantity', $booking->quantity);
            }

            $booking->update(['status' => 'cancelled']);

            DB::commit();

            $this->notificationService->sendBookingCancellation($booking);

            return $this->successResponse(
                new BookingResource($booking),
                'Booking cancelled successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to cancel booking: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get booking by reference (public)
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'exists:bookings,booking_reference'],
        ]);

        $booking = Booking::with(['event', 'ticket', 'payment'])
            ->where('booking_reference', $request->reference)
            ->first();

        return $this->successResponse(
            new BookingResource($booking),
            'Booking retrieved successfully'
        );
    }
}
