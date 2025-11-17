# Sports Hub Backend API

A PHP backend API for a sports court booking system built with PostgreSQL (Neon DB).

## Features

- User authentication with JWT
- Court management
- Booking system with time slot validation
- Admin dashboard with statistics
- PostgreSQL database with proper relationships

## API Endpoints

### Authentication

- `POST /auth/register` - User registration
- `POST /auth/login` - User/admin login

### Courts

- `GET /courts` - List all courts (with optional `?type` filter)
- `GET /courts/{id}/slots?date=YYYY-MM-DD` - Get available time slots

### Bookings

- `POST /bookings` - Create new booking
- `GET /bookings` - Get user's bookings

### Admin (requires admin role)

- `GET /admin/dashboard` - Dashboard stats and recent bookings
- `GET /admin/courts` - List all courts
- `POST /admin/courts` - Add new court
- `DELETE /admin/courts/{id}` - Delete court
- `GET /admin/bookings` - List all bookings (with optional `?status` filter)
- `POST /admin/bookings` - Create manual booking

## Setup

1. Install dependencies:

```bash
composer install
```

2. Copy environment file:

```bash
cp .env.example .env
```

3. Update the `.env` file with your database credentials

4. Start the development server:

```bash
php -S localhost:8000 -t public
```

## Database Schema

The application uses PostgreSQL with the following tables:

- `users` - User accounts with role-based access
- `courts` - Sports courts with pricing and availability
- `bookings` - Court reservations with customer details

## Environment Variables

- `DB_DSN` - PostgreSQL connection string
- `JWT_SECRET` - Secret key for JWT tokens
- `SLOT_START_HOUR` - Booking start hour (default: 6)
- `SLOT_END_HOUR` - Booking end hour (default: 22)
- `SLOT_INTERVAL_MINUTES` - Time slot interval (default: 60)

## API Usage

### Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer <jwt-token>
```

### Example Requests

#### Register User

```json
POST /auth/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "password": "password123"
}
```

#### Create Court (Admin)

```json
POST /admin/courts
{
    "name": "Tennis Court 1",
    "type": "tennis",
    "location": "Main Complex",
    "price_per_hour": 50.00,
    "image_url": "https://example.com/court1.jpg"
}
```

#### Create Booking

```json
POST /bookings
{
    "court_id": 1,
    "booking_date": "2024-01-15",
    "start_time": "10:00",
    "duration": 2,
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "1234567890"
}
```
