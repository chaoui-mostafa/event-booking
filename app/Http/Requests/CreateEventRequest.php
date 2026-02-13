<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'organizer']);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'location' => ['required', 'string', 'max:255'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:draft,published,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after' => 'Event start date must be in the future',
            'end_date.after' => 'Event end date must be after start date',
        ];
    }
}
