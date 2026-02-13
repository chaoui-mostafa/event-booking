<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class EventRepository extends BaseRepository
{
    public function __construct(Event $event)
    {
        parent::__construct($event);
    }

    /**
     * Get published events with filtering
     */
    public function getPublishedEvents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['organizer', 'tickets'])
            ->published()
            ->upcoming();

        // Apply date filter
        if (isset($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        // Apply search
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('location', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply location filter
        if (isset($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get events by organizer
     */
    public function getByOrganizer(int $organizerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['tickets'])
            ->where('organizer_id', $organizerId)
            ->paginate($perPage);
    }
}
