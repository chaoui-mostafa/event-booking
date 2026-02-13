<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class EventController extends BaseController
{
    /**
     * Display a listing of events with filtering
     */
    public function index(Request $request)
    {
        $query = Event::with(['organizer', 'tickets'])
            ->published()
            ->upcoming();

        // Apply date filter
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Apply search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Apply location filter
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Pagination
        $events = $query->paginate($request->per_page ?? 15);

        return $this->successResponse([
            'events' => EventResource::collection($events),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ]
        ], 'Events retrieved successfully');
    }

    /**
     * Store a newly created event
     */
    public function store(CreateEventRequest $request)
    {
        $event = Event::create([
            'organizer_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'location' => $request->location,
            'max_attendees' => $request->max_attendees,
            'status' => $request->status ?? 'draft',
        ]);

        return $this->successResponse(
            new EventResource($event->load(['organizer'])),
            'Event created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        // Only show published events to customers
        if (auth()->check() && auth()->user()->role === 'customer' && $event->status !== 'published') {
            return $this->errorResponse('Event not found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            new EventResource($event->load(['organizer', 'tickets'])),
            'Event retrieved successfully'
        );
    }

    /**
     * Update the specified event
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());

        return $this->successResponse(
            new EventResource($event->load(['organizer'])),
            'Event updated successfully'
        );
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        // Check if user is authorized (admin or organizer who created it)
        if (auth()->user()->role !== 'admin' && auth()->id() !== $event->organizer_id) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        // Check if event has any confirmed bookings
        if ($event->bookings()->where('status', 'confirmed')->exists()) {
            return $this->errorResponse(
                'Cannot delete event with confirmed bookings',
                Response::HTTP_CONFLICT
            );
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully');
    }

    /**
     * Get events created by the authenticated organizer
     */
    public function myEvents(Request $request)
    {
        $events = Event::with(['tickets'])
            ->where('organizer_id', auth()->id())
            ->paginate($request->per_page ?? 15);

        return $this->successResponse([
            'events' => EventResource::collection($events),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ]
        ], 'My events retrieved successfully');
    }

    /**
     * Publish an event
     */
    public function publish(Event $event)
    {
        // Check authorization
        if (auth()->user()->role !== 'admin' && auth()->id() !== $event->organizer_id) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        // Check if event has at least one ticket
        if ($event->tickets()->count() === 0) {
            return $this->errorResponse(
                'Cannot publish event without tickets',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $event->update(['status' => 'published']);

        return $this->successResponse(
            new EventResource($event),
            'Event published successfully'
        );
    }

    /**
     * Cancel an event
     */
    public function cancel(Event $event)
    {
        // Check authorization
        if (auth()->user()->role !== 'admin' && auth()->id() !== $event->organizer_id) {
            return $this->errorResponse('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        $event->update(['status' => 'cancelled']);

        return $this->successResponse(
            new EventResource($event),
            'Event cancelled successfully'
        );
    }
}
