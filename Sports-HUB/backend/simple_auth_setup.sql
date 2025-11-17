-- Simple Authentication Setup
-- Plain text passwords (no encryption)

-- Clear existing users
DELETE FROM users;

-- Create simple test accounts
INSERT INTO users (name, email, phone, password, role, created_at) VALUES
('Admin User', 'admin@sportzhub.com', '1234567890', '123', 'admin', NOW()),
('Shahzaib Admin', 'shahzaib@admin.com', '1234567890', '123', 'admin', NOW()),
('Test User', 'user@sportzhub.com', '1234567890', '123', 'user', NOW()),
('John Doe', 'john@example.com', '1234567890', 'password', 'user', NOW());

-- Verify
SELECT id, name, email, password, role FROM users ORDER BY role, id;
