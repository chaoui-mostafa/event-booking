<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'sales_end_at' => $this->sales_end_at?->toISOString(),
            'is_available' => $this->available_quantity > 0,
            'event' => new EventResource($this->whenLoaded('event')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
