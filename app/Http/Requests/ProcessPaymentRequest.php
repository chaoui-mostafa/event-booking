<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');
        return $this->user() && $this->user()->id === $booking->user_id;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:credit_card,paypal,bank_transfer'],
            'card_number' => ['required_if:payment_method,credit_card', 'string', 'size:16'],
            'card_expiry' => ['required_if:payment_method,credit_card', 'string', 'regex:/^\d{2}\/\d{2}$/'],
            'card_cvv' => ['required_if:payment_method,credit_card', 'string', 'size:3'],
            'paypal_email' => ['required_if:payment_method,paypal', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.required_if' => 'Card number is required',
            'card_expiry.required_if' => 'Card expiry is required',
            'card_cvv.required_if' => 'CVV is required',
            'paypal_email.required_if' => 'PayPal email is required',
        ];
    }
}
