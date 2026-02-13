<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support dropping or modifying constraints easily
        // For SQLite, we need to recreate the table
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Create temporary table
            Schema::create('bookings_new', function (Blueprint $table) {
                $table->id();
                $table->string('booking_reference')->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('event_id')->constrained()->onDelete('cascade');
                $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('total_amount', 10, 2);
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('booking_reference');
            });

            // Copy data
            DB::table('bookings_new')->insert(
                DB::table('bookings')->select('*')->get()->map(function ($item) {
                    return (array) $item;
                })->toArray()
            );

            // Drop old table and rename new one
            Schema::drop('bookings');
            Schema::rename('bookings_new', 'bookings');
        }
    }

    public function down(): void
    {
        // Revert if needed
    }
};
