<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'available_quantity',
        'sales_end_at'
    ];

    protected $casts = [
        'sales_end_at' => 'datetime',
        'price' => 'decimal:2'
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Check if ticket is available
    public function isAvailable(int $quantity): bool
    {
        return $this->available_quantity >= $quantity &&
               (!$this->sales_end_at || $this->sales_end_at->isFuture());
    }

    // Decrease available quantity
    public function decreaseAvailableQuantity(int $quantity): bool
    {
        if (!$this->isAvailable($quantity)) {
            return false;
        }

        $this->decrement('available_quantity', $quantity);
        return true;
    }
}
