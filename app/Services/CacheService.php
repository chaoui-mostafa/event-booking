<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache tags for different entities
     */
    const TAG_EVENTS = 'events';
    const TAG_TICKETS = 'tickets';
    const TAG_BOOKINGS = 'bookings';

    /**
     * Cache TTL in minutes
     */
    const TTL_EVENTS = 30;
    const TTL_TICKETS = 15;
    const TTL_PUBLIC = 60;

    /**
     * Get or remember events with filtering
     */
    public function rememberEvents(string $cacheKey, \Closure $callback)
    {
        return Cache::tags([self::TAG_EVENTS])->remember(
            $cacheKey,
            now()->addMinutes(self::TTL_EVENTS),
            $callback
        );
    }

    /**
     * Get or remember event details
     */
    public function rememberEvent(int $eventId, \Closure $callback)
    {
        return Cache::tags([self::TAG_EVENTS, "event.{$eventId}"])->remember(
            "event.{$eventId}",
            now()->addMinutes(self::TTL_EVENTS),
            $callback
        );
    }

    /**
     * Get or remember tickets for event
     */
    public function rememberTickets(int $eventId, \Closure $callback)
    {
        return Cache::tags([self::TAG_TICKETS, "event.{$eventId}"])->remember(
            "event.{$eventId}.tickets",
            now()->addMinutes(self::TTL_TICKETS),
            $callback
        );
    }

    /**
     * Clear cache for event
     */
    public function clearEventCache(int $eventId): void
    {
        Cache::tags([self::TAG_EVENTS, "event.{$eventId}"])->flush();
    }

    /**
     * Clear all events cache
     */
    public function clearAllEventsCache(): void
    {
        Cache::tags([self::TAG_EVENTS])->flush();
    }

    /**
     * Clear tickets cache for event
     */
    public function clearTicketsCache(int $eventId): void
    {
        Cache::tags([self::TAG_TICKETS, "event.{$eventId}"])->flush();
    }
}
