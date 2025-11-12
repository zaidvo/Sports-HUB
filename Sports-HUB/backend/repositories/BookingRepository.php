<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/Booking.php';

class BookingRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(array $bookingData): Booking
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO bookings (user_id, court_id, slot_id, amount_paid, payment_status, confirmation_message, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING *'
        );
        
        $stmt->execute([
            $bookingData['user_id'],
            $bookingData['court_id'],
            $bookingData['slot_id'],
            $bookingData['amount_paid'] ?? null,
            $bookingData['payment_status'] ?? 'Pending',
            $bookingData['confirmation_message'] ?? null,
            $bookingData['status'] ?? 'Confirmed'
        ]);
        
        $data = $stmt->fetch();
        return new Booking($data);
    }

    public function findById(int $bookingId): ?Booking
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bookings WHERE booking_id = ?');
        $stmt->execute([$bookingId]);
        $data = $stmt->fetch();
        
        return $data ? new Booking($data) : null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, c.court_name, c.court_type, c.location, 
                    s.date, s.start_time, s.end_time
             FROM bookings b
             JOIN courts c ON b.court_id = c.court_id
             JOIN available_slots s ON b.slot_id = s.slot_id
             WHERE b.user_id = ?
             ORDER BY b.booking_date DESC'
        );
        $stmt->execute([$userId]);
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $data; // Return as array for detailed booking info
        }
        
        return $bookings;
    }

    public function findAllWithDetails(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, u.full_name, u.phone_number, u.email,
                    c.court_name, c.court_type, c.location,
                    s.date, s.start_time, s.end_time
             FROM bookings b
             JOIN users u ON b.user_id = u.user_id
             JOIN courts c ON b.court_id = c.court_id
             JOIN available_slots s ON b.slot_id = s.slot_id
             ORDER BY b.booking_date DESC'
        );
        $stmt->execute();
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $data;
        }
        
        return $bookings;
    }

    public function update(int $bookingId, array $bookingData): ?Booking
    {
        $fields = [];
        $values = [];
        
        $allowedFields = ['payment_status', 'confirmation_message', 'status', 'amount_paid'];
        
        foreach ($allowedFields as $field) {
            if (isset($bookingData[$field])) {
                $fields[] = "$field = ?";
                $values[] = $bookingData[$field];
            }
        }
        
        if (empty($fields)) {
            return $this->findById($bookingId);
        }
        
        $values[] = $bookingId;
        $sql = 'UPDATE bookings SET ' . implode(', ', $fields) . ' WHERE booking_id = ? RETURNING *';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        $data = $stmt->fetch();
        
        return $data ? new Booking($data) : null;
    }

    public function delete(int $bookingId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM bookings WHERE booking_id = ?');
        return $stmt->execute([$bookingId]);
    }

    public function getTodaysBookings(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, u.full_name, c.court_name, s.start_time, s.end_time
             FROM bookings b
             JOIN users u ON b.user_id = u.user_id
             JOIN courts c ON b.court_id = c.court_id
             JOIN available_slots s ON b.slot_id = s.slot_id
             WHERE s.date = CURRENT_DATE
             ORDER BY s.start_time'
        );
        $stmt->execute();
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $data;
        }
        
        return $bookings;
    }

    public function getBookingStats(): array
    {
        // Total bookings
        $stmt = $this->pdo->query('SELECT COUNT(*) as total_bookings FROM bookings');
        $totalBookings = $stmt->fetch()['total_bookings'];

        // Total revenue
        $stmt = $this->pdo->query('SELECT SUM(amount_paid) as total_revenue FROM bookings WHERE payment_status = \'Paid\'');
        $totalRevenue = $stmt->fetch()['total_revenue'] ?? 0;

        // Today's bookings
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as todays_bookings 
             FROM bookings b 
             JOIN available_slots s ON b.slot_id = s.slot_id 
             WHERE s.date = CURRENT_DATE'
        );
        $stmt->execute();
        $todaysBookings = $stmt->fetch()['todays_bookings'];

        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'todays_bookings' => $todaysBookings
        ];
    }
}