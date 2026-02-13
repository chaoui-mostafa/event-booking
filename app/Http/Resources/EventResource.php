<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date->toISOString(),
            'end_date' => $this->end_date->toISOString(),
            'location' => $this->location,
            'max_attendees' => $this->max_attendees,
            'status' => $this->status,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'total_tickets_available' => $this->whenLoaded('tickets', function () {
                return $this->tickets->sum('available_quantity');
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
