<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_view_published_events()
    {
        // Clear existing events
        Event::query()->delete();

        // Create 3 published events
        Event::factory()->count(3)->published()->create();

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'events',
                    'pagination'
                ]
            ]);

        $this->assertCount(3, $response->json('data.events'));
    }

    public function test_anyone_can_search_events()
    {
        Event::query()->delete();

        Event::factory()->create([
            'title' => 'Music Festival 2024',
            'status' => 'published'
        ]);

        Event::factory()->create([
            'title' => 'Tech Conference',
            'status' => 'published'
        ]);

        $response = $this->getJson('/api/v1/events?search=Music');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.events'));
        $this->assertEquals('Music Festival 2024', $response->json('data.events.0.title'));
    }

    public function test_organizer_can_create_event()
    {
        $organizer = User::factory()->organizer()->create();
        $token = $organizer->createToken('test-token')->plainTextToken;

        $eventData = [
            'title' => 'New Event',
            'description' => 'Event Description',
            'start_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(32)->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
            'max_attendees' => 100,
            'status' => 'draft'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/events', $eventData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Event created successfully'
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'New Event',
            'organizer_id' => $organizer->id
        ]);
    }

    public function test_customer_cannot_create_event()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/events', [
                'title' => 'New Event',
                'description' => 'Description',
                'start_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'end_date' => now()->addDays(32)->format('Y-m-d H:i:s'),
                'location' => 'Location'
            ]);

        $response->assertStatus(403);
    }

    public function test_organizer_can_update_their_event()
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'draft'
        ]);

        $token = $organizer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/events/' . $event->id, [
                'title' => 'Updated Event Title'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title'
        ]);
    }

    public function test_organizer_can_publish_event_with_tickets()
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'status' => 'draft'
        ]);

        Ticket::factory()->count(2)->create(['event_id' => $event->id]);

        $token = $organizer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/v1/events/' . $event->id . '/publish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event published successfully'
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => 'published'
        ]);
    }
}
