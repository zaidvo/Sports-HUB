<?php
require_once __DIR__ . '/Database.php';

class DatabaseInitializer
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function initializeDatabase(): void
    {
        echo "Initializing database...\n";
        
        $this->createTables();
        $this->insertSampleData();
        
        echo "Database initialized successfully!\n";
    }

    private function createTables(): void
    {
        // Detect database type and use appropriate schema
        $config = require __DIR__ . '/../config/config.php';
        $url = $config['database_url'];
        
        if (str_starts_with($url, 'sqlite:')) {
            $schema = file_get_contents(__DIR__ . '/schema-sqlite.sql');
        } else {
            $schema = file_get_contents(__DIR__ . '/schema.sql');
        }
        
        $this->pdo->exec($schema);
        echo "Tables created.\n";
    }

    private function insertSampleData(): void
    {
        // Insert sample admin
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $this->pdo->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (?, ?) 
             ON CONFLICT (username) DO NOTHING'
        )->execute(['admin', $adminPassword]);

        // Insert sample users
        $users = [
            ['John Doe', '1234567890', 'john@example.com', password_hash('password', PASSWORD_DEFAULT)],
            ['Jane Smith', '0987654321', 'jane@example.com', password_hash('password', PASSWORD_DEFAULT)],
            ['Mike Johnson', '5555555555', 'mike@example.com', password_hash('password', PASSWORD_DEFAULT)]
        ];

        foreach ($users as $user) {
            $this->pdo->prepare(
                'INSERT INTO users (full_name, phone_number, email, password_hash) VALUES (?, ?, ?, ?)
                 ON CONFLICT (email) DO NOTHING'
            )->execute($user);
        }

        // Insert sample courts
        $courts = [
            ['Futsal Court A', 'Futsal', 'Downtown Sports Complex', 50.00, 'Professional futsal court with artificial grass', '/images/futsal1.jpg'],
            ['Futsal Court B', 'Futsal', 'Downtown Sports Complex', 45.00, 'Indoor futsal court with high-quality flooring', '/images/futsal2.jpg'],
            ['Badminton Court 1', 'Badminton', 'City Sports Center', 30.00, 'Indoor badminton court with wooden floor', '/images/badminton1.jpg'],
            ['Badminton Court 2', 'Badminton', 'City Sports Center', 30.00, 'Indoor badminton court with proper ventilation', '/images/badminton2.jpg'],
            ['Padel Court Elite', 'Padel', 'Premium Sports Club', 60.00, 'Premium padel court with glass walls', '/images/padel1.jpg'],
            ['Tennis Court Central', 'Tennis', 'Central Park Complex', 40.00, 'Outdoor tennis court with clay surface', '/images/tennis1.jpg']
        ];

        foreach ($courts as $court) {
            $this->pdo->prepare(
                'INSERT INTO courts (court_name, court_type, location, price_per_hour, description, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute($court);
        }

        // Insert sample time slots for next 7 days
        $this->generateTimeSlots();

        echo "Sample data inserted.\n";
    }

    private function generateTimeSlots(): void
    {
        // Get all courts
        $courts = $this->pdo->query('SELECT court_id FROM courts')->fetchAll(PDO::FETCH_COLUMN);
        
        // Generate slots for next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            
            foreach ($courts as $courtId) {
                // Generate hourly slots from 8 AM to 10 PM
                for ($hour = 8; $hour < 22; $hour++) {
                    $startTime = sprintf('%02d:00', $hour);
                    $endTime = sprintf('%02d:00', $hour + 1);
                    
                    try {
                        $this->pdo->prepare(
                            'INSERT INTO available_slots (court_id, date, start_time, end_time) 
                             VALUES (?, ?, ?, ?)'
                        )->execute([$courtId, $date, $startTime, $endTime]);
                    } catch (PDOException $e) {
                        // Slot might already exist, continue
                        continue;
                    }
                }
            }
        }

        echo "Time slots generated.\n";
    }
}

// Run if called directly
if (php_sapi_name() === 'cli') {
    $initializer = new DatabaseInitializer();
    $initializer->initializeDatabase();
}