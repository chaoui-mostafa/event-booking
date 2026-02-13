@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-primary-600 to-primary-800 rounded-3xl overflow-hidden mb-12 animate-fade-in">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-primary-800 mix-blend-multiply"></div>
        </div>
        <div class="relative px-6 py-16 sm:px-12 sm:py-24 lg:px-16">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Discover Amazing Events
            </h1>
            <p class="mt-6 max-w-3xl text-xl text-primary-100">
                Book tickets for the best concerts, conferences, workshops, and events in your city.
            </p>
            <div class="mt-10">
                <a href="{{ route('events.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-700 bg-white hover:bg-gray-50 transition-all transform hover:scale-105">
                    Browse Events
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="mb-12 animate-slide-up">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Why Choose Us</h2>
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Easy Booking</h3>
                <p class="text-gray-600">Book your tickets in just a few clicks with our streamlined booking process.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Secure Payments</h3>
                <p class="text-gray-600">Your payments are safe and secure with our encrypted payment system.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">24/7 Support</h3>
                <p class="text-gray-600">Our support team is always here to help you with any questions.</p>
            </div>
        </div>
    </div>

    <!-- Upcoming Events Preview -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Upcoming Events</h2>
            <a href="{{ route('events.index') }}" class="text-primary-600 hover:text-primary-700 font-medium flex items-center">
                View All
                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        @php
            $featuredEvents = \App\Models\Event::with(['organizer', 'tickets'])
                ->published()
                ->upcoming()
                ->take(3)
                ->get();
        @endphp

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach($featuredEvents as $event)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="h-48 bg-gradient-to-br from-primary-500 to-primary-700 relative">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="absolute bottom-4 left-4">
                        <span class="px-3 py-1 bg-white text-primary-600 rounded-full text-sm font-medium">
                            {{ $event->tickets->count() }} ticket types
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $event->start_date->format('M d, Y') }}
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $event->title }}</h3>
                    <p class="text-gray-600 mb-4 line-clamp-2">{{ $event->description }}</p>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $event->location }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary-600">
                            ${{ number_format($event->tickets->min('price'), 2) }}+
                        </span>
                        <a href="{{ route('events.show', $event) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gray-900 rounded-3xl overflow-hidden mb-12">
        <div class="px-6 py-16 sm:px-12 sm:py-24 lg:px-16 text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Ready to create your own event?
            </h2>
            <p class="mt-4 text-lg text-gray-300">
                Join our platform as an organizer and start selling tickets today.
            </p>
            <div class="mt-8">
                @auth
                    @if(auth()->user()->isOrganizer() || auth()->user()->isAdmin())
                    <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-700 bg-white hover:bg-gray-50">
                        Create Event
                    </a>
                    @else
                    <a href="#" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-700 bg-white hover:bg-gray-50">
                        Become an Organizer
                    </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-700 bg-white hover:bg-gray-50">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
