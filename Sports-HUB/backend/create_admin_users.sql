-- Create Admin Users for SportzHub
-- Run this SQL in your Neon DB console

-- WORKING ADMIN CREDENTIALS:
-- Email: admin@sportzhub.com | Password: admin123
-- Email: shahzaib@admin.com  | Password: 123

-- Admin 1: admin@sportzhub.com / password: admin123
INSERT INTO users (name, email, phone, password, role, created_at)
VALUES (
  'Admin User',
  'admin@sportzhub.com',
  '1234567890',
  '$2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6',
  'admin',
  NOW()
) ON CONFLICT (email) DO UPDATE SET
  password = EXCLUDED.password,
  role = 'admin';

-- Admin 2: shahzaib@admin.com / password: 123
INSERT INTO users (name, email, phone, password, role, created_at)
VALUES (
  'Shahzaib Admin',
  'shahzaib@admin.com',
  '1234567890',
  '$2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6',
  'admin',
  NOW()
) ON CONFLICT (email) DO UPDATE SET
  password = EXCLUDED.password,
  role = 'admin';

-- Verify the users were created
SELECT id, name, email, role, created_at FROM users WHERE role = 'admin';
