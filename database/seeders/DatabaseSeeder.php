<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create organizer
        $organizer = User::factory()->organizer()->create([
            'name' => 'Event Organizer',
            'email' => 'organizer@example.com',
        ]);

        // Create customer
        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'role' => 'customer'
        ]);

        // Create events with tickets
        $events = Event::factory()
            ->count(3)
            ->forOrganizer($organizer->id)
            ->published()
            ->create();

        foreach ($events as $event) {
            Ticket::factory()
                ->count(2)
                ->forEvent($event->id)
                ->create();
        }
    }
}
