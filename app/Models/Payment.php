<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reference',
        'booking_id',
        'amount',
        'payment_method',
        'status',
        'payment_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array'
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Generate unique payment reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->payment_reference = 'PAY-' . strtoupper(uniqid());
        });
    }
}
