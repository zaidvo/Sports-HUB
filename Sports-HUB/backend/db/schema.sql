-- SportzHub Database Schema (PostgreSQL)
-- Run this on your Neon database

CREATE TABLE IF NOT EXISTS users (
  user_id SERIAL PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(100) UNIQUE,
  password_hash TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
  admin_id SERIAL PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courts (
  court_id SERIAL PRIMARY KEY,
  court_name VARCHAR(100) NOT NULL,
  court_type VARCHAR(50) NOT NULL,
  location VARCHAR(100) NOT NULL,
  price_per_hour DECIMAL(10,2) NOT NULL,
  description TEXT,
  image_url TEXT,
  is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS available_slots (
  slot_id SERIAL PRIMARY KEY,
  court_id INT REFERENCES courts(court_id) ON DELETE CASCADE,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_booked BOOLEAN DEFAULT FALSE,
  UNIQUE (court_id, date, start_time, end_time)
);

CREATE TABLE IF NOT EXISTS bookings (
  booking_id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(user_id) ON DELETE CASCADE,
  court_id INT REFERENCES courts(court_id) ON DELETE CASCADE,
  slot_id INT REFERENCES available_slots(slot_id) ON DELETE RESTRICT,
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  amount_paid DECIMAL(10,2),
  payment_status VARCHAR(20) DEFAULT 'Pending',
  confirmation_message TEXT,
  status VARCHAR(20) DEFAULT 'Confirmed'
);

CREATE TABLE IF NOT EXISTS payments (
  payment_id SERIAL PRIMARY KEY,
  booking_id INT REFERENCES bookings(booking_id) ON DELETE CASCADE,
  payment_method VARCHAR(50),
  transaction_id VARCHAR(100) UNIQUE,
  amount DECIMAL(10,2),
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Helpful indexes
CREATE INDEX IF NOT EXISTS idx_slots_court_date ON available_slots(court_id, date);
CREATE INDEX IF NOT EXISTS idx_bookings_user ON bookings(user_id);
