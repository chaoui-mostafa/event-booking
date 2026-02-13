<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        $admin = User::factory()->admin()->create([
            'email' => 'test.admin@example.com',
        ]);

        $organizer = User::factory()->organizer()->create([
            'email' => 'test.organizer@example.com',
        ]);

        $customer = User::factory()->create([
            'email' => 'test.customer@example.com',
        ]);

        // Create test event with tickets
        $event = Event::factory()
            ->forOrganizer($organizer->id)
            ->published()
            ->create([
                'title' => 'Test Event for Testing',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(12),
            ]);

        Ticket::factory()
            ->count(3)
            ->forEvent($event->id)
            ->create([
                'price' => 50.00,
                'quantity' => 100,
                'available_quantity' => 100,
            ]);
    }
}
