<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user() && (
            $this->user()->role === 'admin' ||
            $this->user()->id === $event->organizer_id
        );
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'start_date' => ['sometimes', 'date', 'after:now'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:draft,published,cancelled'],
        ];
    }
}
