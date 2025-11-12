# SportzHub Backend API

This is the backend API for the SportzHub sports court booking system.

## Features

- **User Authentication**: Login, registration, and admin authentication
- **Court Management**: CRUD operations for sports courts
- **Booking System**: Create, manage, and track court bookings
- **Time Slot Management**: Generate and manage available time slots
- **Admin Dashboard**: Statistics and management interface

## API Endpoints

### Authentication (`/auth`)

- `POST /auth/login` - User/Admin login
- `POST /auth/register` - User registration
- `POST /auth/logout` - Logout
- `POST /auth/create-admin` - Create admin (should be protected)

### Courts (`/courts`)

- `GET /courts` - Get all courts (with filters)
- `GET /courts/{id}` - Get court by ID
- `GET /courts/{id}/slots?date=YYYY-MM-DD` - Get available slots for court
- `GET /courts/search?type=&location=&max_price=` - Search courts
- `GET /courts/types` - Get all court types
- `GET /courts/locations` - Get all locations

### Bookings (`/bookings`)

- `POST /bookings` - Create new booking
- `GET /bookings?user_id=` - Get user bookings
- `GET /bookings/{id}` - Get booking by ID
- `PUT /bookings/{id}` - Update booking
- `POST /bookings/{id}/cancel` - Cancel booking

### Admin (`/admin`)

- `GET /admin/dashboard` - Get dashboard statistics
- `GET /admin/courts` - Get all courts (including inactive)
- `POST /admin/courts` - Create new court
- `PUT /admin/courts/{id}` - Update court
- `DELETE /admin/courts/{id}` - Delete court (soft delete)
- `POST /admin/courts/{id}/slots` - Generate time slots for court
- `GET /admin/bookings` - Get all bookings
- `GET /admin/bookings/today` - Get today's bookings
- `PUT /admin/bookings/{id}` - Update booking

## Request/Response Examples

### Login Request

```json
POST /auth/login
{
    "email": "user@example.com",
    "password": "password"
}
```

### Login Response

```json
{
  "success": true,
  "token": "abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "user"
  }
}
```

### Create Booking Request

```json
POST /bookings
{
    "user_id": 1,
    "slot_id": 5,
    "payment_status": "Pending"
}
```

### Create Court Request (Admin)

```json
POST /admin/courts
{
    "court_name": "New Futsal Court",
    "court_type": "Futsal",
    "location": "Sports Complex",
    "price_per_hour": 50.00,
    "description": "Professional futsal court",
    "image_url": "/images/court.jpg"
}
```

## Database Schema

The system uses PostgreSQL with the following main tables:

- `users` - User accounts
- `admins` - Admin accounts
- `courts` - Sports courts
- `available_slots` - Time slots for courts
- `bookings` - Court bookings
- `payments` - Payment records

## Setup Instructions

1. **Database Setup**:

   - Create a PostgreSQL database
   - Update the `DATABASE_URL` in your `.env` file
   - Run the schema: `php db/init.php`

2. **Environment Configuration**:

   - Copy `config/env.example` to `config/.env`
   - Update database and other settings

3. **Web Server**:

   - Point your web server to the `public` directory
   - Ensure URL rewriting is enabled (Apache mod_rewrite)

4. **Test the API**:
   - Visit `/api/courts` to test the courts endpoint
   - Use `/api/auth/login` with demo credentials

## Demo Credentials

### Admin Login:

- Username: `admin@test.com` (password: anything)

### User Login:

- Email: `user@test.com` (password: anything)

## Security Features

- Password hashing using PHP's `password_hash()`
- CORS support for frontend integration
- Input validation and sanitization
- SQL injection prevention with prepared statements
- Error handling with proper HTTP status codes

## Technology Stack

- **PHP 8.0+** - Backend language
- **PostgreSQL** - Database
- **PDO** - Database abstraction
- **RESTful API** - API architecture
- **JSON** - Data format

## File Structure

```
backend/
├── config/          # Configuration files
├── controllers/     # API controllers
│   ├── admin/       # Admin controllers
│   ├── booking/     # Booking controllers
│   └── user/        # User controllers
├── db/              # Database files
├── models/          # Data models
├── repositories/    # Data access layer
├── services/        # Business logic layer
└── public/          # Web-accessible files
    └── api/         # API entry point
```
