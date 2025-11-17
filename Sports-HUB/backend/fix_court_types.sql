-- Fix Court Type Names to Match Frontend
-- Run this in your Neon DB console

-- Update court types to proper case (case-insensitive search)
UPDATE courts SET type = 'Futsal' WHERE LOWER(type) LIKE '%futsal%';
UPDATE courts SET type = 'Badminton' WHERE LOWER(type) LIKE '%badminton%';
UPDATE courts SET type = 'Padel' WHERE LOWER(type) LIKE '%padel%';
UPDATE courts SET type = 'Cricket' WHERE LOWER(type) LIKE '%cricket%';
UPDATE courts SET type = 'Tennis' WHERE LOWER(type) LIKE '%tennis%';

-- Verify the changes
SELECT id, name, type, location, price_per_hour, status FROM courts ORDER BY type, id;
