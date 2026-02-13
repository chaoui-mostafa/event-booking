<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'sales_end_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
