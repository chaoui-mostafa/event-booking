<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class TicketController extends BaseController
{
    /**
     * Display tickets for a specific event
     */
    public function index(Event $event)
    {
        $tickets = $event->tickets()->paginate(request()->per_page ?? 15);

        return $this->successResponse([
            'tickets' => TicketResource::collection($tickets),
            'pagination' => [
                'total' => $tickets->total(),
                'per_page' => $tickets->perPage(),
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
            ]
        ], 'Tickets retrieved successfully');
    }

    /**
     * Store a newly created ticket for an event
     */
    public function store(CreateTicketRequest $request, Event $event)
    {
        $ticket = $event->tickets()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'available_quantity' => $request->quantity,
            'sales_end_at' => $request->sales_end_at,
        ]);

        Cache::tags(['events'])->flush();

        return $this->successResponse(
            new TicketResource($ticket),
            'Ticket created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified ticket
     */
    public function show(Event $event, Ticket $ticket)
    {
        // Ensure ticket belongs to event
        if ($ticket->event_id !== $event->id) {
            return $this->errorResponse('Ticket not found for this event', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            new TicketResource($ticket->load('event')),
            'Ticket retrieved successfully'
        );
    }

    /**
     * Update the specified ticket
     */
    public function update(UpdateTicketRequest $request, Event $event, Ticket $ticket)
    {
        // Ensure ticket belongs to event
        if ($ticket->event_id !== $event->id) {
            return $this->errorResponse('Ticket not found for this event', Response::HTTP_NOT_FOUND);
        }

        // Check if ticket has any bookings
        if ($ticket->bookings()->where('status', 'confirmed')->exists()) {
            return $this->errorResponse(
                'Cannot update ticket with confirmed bookings',
                Response::HTTP_CONFLICT
            );
        }

        $data = $request->validated();

        // If quantity is updated, adjust available_quantity accordingly
        if (isset($data['quantity'])) {
            $soldQuantity = $ticket->quantity - $ticket->available_quantity;
            $data['available_quantity'] = $data['quantity'] - $soldQuantity;

            if ($data['available_quantity'] < 0) {
                return $this->errorResponse(
                    'New quantity cannot be less than tickets already sold',
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $ticket->update($data);

        Cache::tags(['events'])->flush();

        return $this->successResponse(
            new TicketResource($ticket),
            'Ticket updated successfully'
        );
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(Event $event, Ticket $ticket)
    {
        // Ensure ticket belongs to event
        if ($ticket->event_id !== $event->id) {
            return $this->errorResponse('Ticket not found for this event', Response::HTTP_NOT_FOUND);
        }

        // Check if ticket has any bookings
        if ($ticket->bookings()->exists()) {
            return $this->errorResponse(
                'Cannot delete ticket with existing bookings',
                Response::HTTP_CONFLICT
            );
        }

        $ticket->delete();

        Cache::tags(['events'])->flush();

        return $this->successResponse(null, 'Ticket deleted successfully');
    }

    /**
     * Get available tickets for an event
     */
    public function available(Event $event)
    {
        $tickets = $event->tickets()
            ->where('available_quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('sales_end_at')
                    ->orWhere('sales_end_at', '>', now());
            })
            ->get();

        return $this->successResponse(
            TicketResource::collection($tickets),
            'Available tickets retrieved successfully'
        );
    }
}
