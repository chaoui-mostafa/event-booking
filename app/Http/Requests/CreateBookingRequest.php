<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'exists:tickets,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.required' => 'Please select a ticket',
            'ticket_id.exists' => 'Selected ticket is invalid',
            'quantity.max' => 'Maximum 10 tickets per booking',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ticket = \App\Models\Ticket::find($this->ticket_id);
            $event = $this->route('event');

            if (!$ticket || $ticket->event_id !== $event->id) {
                $validator->errors()->add('ticket_id', 'Ticket does not belong to this event');
            }

            if ($ticket && !$ticket->isAvailable($this->quantity)) {
                $validator->errors()->add('quantity', 'Not enough tickets available');
            }
        });
    }
}
