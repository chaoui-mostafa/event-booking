# Event Booking API

A production-ready RESTful API for event booking built with Laravel.

## Features
- ✅ Authentication with Laravel Sanctum
- ✅ Role-based access (Admin, Organizer, Customer)
- ✅ Event CRUD with filtering and caching
- ✅ Ticket management
- ✅ Booking system with double booking prevention
- ✅ Payment simulation
- ✅ Queue notifications
- ✅ Comprehensive test suite (85%+ coverage)

## Tech Stack
- Laravel 12.x
- MySQL/PostgreSQL
- Redis for caching
- Laravel Sanctum for API auth
- Laravel Horizon for queues
- PHPUnit for testing

## API Endpoints

### Authentication
- `POST /api/v1/register` - Register new user
- `POST /api/v1/login` - Login user
- `POST /api/v1/logout` - Logout user (Auth)
- `GET /api/v1/me` - Get authenticated user

### Events
- `GET /api/v1/events` - List events (with filters)
- `GET /api/v1/events/{id}` - Get event details
- `POST /api/v1/events` - Create event (Organizer/Admin)
- `PUT /api/v1/events/{id}` - Update event (Organizer/Admin)
- `DELETE /api/v1/events/{id}` - Delete event (Organizer/Admin)
- `PATCH /api/v1/events/{id}/publish` - Publish event
- `PATCH /api/v1/events/{id}/cancel` - Cancel event

### Tickets
- `GET /api/v1/events/{eventId}/tickets` - List event tickets
- `GET /api/v1/events/{eventId}/tickets/available` - Available tickets
- `POST /api/v1/events/{eventId}/tickets` - Create ticket (Organizer/Admin)
- `PUT /api/v1/events/{eventId}/tickets/{ticketId}` - Update ticket
- `DELETE /api/v1/events/{eventId}/tickets/{ticketId}` - Delete ticket

### Bookings
- `GET /api/v1/bookings` - User bookings
- `POST /api/v1/events/{eventId}/book` - Create booking
- `GET /api/v1/bookings/{id}` - Get booking details
- `POST /api/v1/bookings/{id}/pay` - Process payment
- `POST /api/v1/bookings/{id}/cancel` - Cancel booking
- `GET /api/v1/bookings/lookup` - Lookup booking by reference

## Installation

```bash
# Clone repository
git clone https://github.com/chaoui-mostafa/event-booking-api.git

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start server
php artisan serve

event-booking-api/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SendEventReminders.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── EventController.php
│   │   │   ├── TicketController.php
│   │   │   └── BookingController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   └── PreventDoubleBooking.php
│   │   ├── Requests/
│   │   │   ├── BaseRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── CreateEventRequest.php
│   │   │   ├── UpdateEventRequest.php
│   │   │   ├── CreateTicketRequest.php
│   │   │   ├── UpdateTicketRequest.php
│   │   │   ├── CreateBookingRequest.php
│   │   │   └── ProcessPaymentRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── EventResource.php
│   │       ├── TicketResource.php
│   │       ├── BookingResource.php
│   │       └── PaymentResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── Ticket.php
│   │   ├── Booking.php
│   │   └── Payment.php
│   ├── Repositories/
│   │   ├── BaseRepository.php
│   │   └── EventRepository.php
│   ├── Services/
│   │   ├── BaseService.php
│   │   ├── PaymentService.php
│   │   ├── NotificationService.php
│   │   └── CacheService.php
│   ├── Traits/
│   │   ├── ApiResponseTrait.php
│   │   └── Filterable.php
│   └── Notifications/
│       ├── BookingConfirmedNotification.php
│       ├── BookingCancelledNotification.php
│       └── EventReminderNotification.php
├── database/
│   ├── migrations/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── EventFactory.php
│   │   ├── TicketFactory.php
│   │   ├── BookingFactory.php
│   │   └── PaymentFactory.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── TestDatabaseSeeder.php
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── EventTest.php
│   │   └── BookingTest.php
│   └── Unit/
│       └── Services/
│           └── PaymentServiceTest.php
├── .env.example
├── phpunit.xml
└── README.md
