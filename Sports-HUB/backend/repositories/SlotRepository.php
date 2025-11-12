<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/AvailableSlot.php';

class SlotRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findByCourtAndDate(int $courtId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM available_slots 
             WHERE court_id = ? AND date = ? 
             ORDER BY start_time'
        );
        $stmt->execute([$courtId, $date]);
        
        $slots = [];
        while ($data = $stmt->fetch()) {
            $slots[] = new AvailableSlot($data);
        }
        
        return $slots;
    }

    public function findAvailableSlots(int $courtId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM available_slots 
             WHERE court_id = ? AND date = ? AND is_booked = false 
             ORDER BY start_time'
        );
        $stmt->execute([$courtId, $date]);
        
        $slots = [];
        while ($data = $stmt->fetch()) {
            $slots[] = new AvailableSlot($data);
        }
        
        return $slots;
    }

    public function findById(int $slotId): ?AvailableSlot
    {
        $stmt = $this->pdo->prepare('SELECT * FROM available_slots WHERE slot_id = ?');
        $stmt->execute([$slotId]);
        $data = $stmt->fetch();
        
        return $data ? new AvailableSlot($data) : null;
    }

    public function create(array $slotData): AvailableSlot
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO available_slots (court_id, date, start_time, end_time) 
             VALUES (?, ?, ?, ?) RETURNING *'
        );
        
        $stmt->execute([
            $slotData['court_id'],
            $slotData['date'],
            $slotData['start_time'],
            $slotData['end_time']
        ]);
        
        $data = $stmt->fetch();
        return new AvailableSlot($data);
    }

    public function markAsBooked(int $slotId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE available_slots SET is_booked = true WHERE slot_id = ?');
        return $stmt->execute([$slotId]);
    }

    public function markAsAvailable(int $slotId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE available_slots SET is_booked = false WHERE slot_id = ?');
        return $stmt->execute([$slotId]);
    }

    public function generateSlotsForCourt(int $courtId, string $date, array $timeSlots): array
    {
        $createdSlots = [];
        
        foreach ($timeSlots as $slot) {
            try {
                $createdSlot = $this->create([
                    'court_id' => $courtId,
                    'date' => $date,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time']
                ]);
                $createdSlots[] = $createdSlot;
            } catch (PDOException $e) {
                // Slot might already exist (UNIQUE constraint), skip it
                continue;
            }
        }
        
        return $createdSlots;
    }

    public function deleteSlot(int $slotId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM available_slots WHERE slot_id = ? AND is_booked = false');
        return $stmt->execute([$slotId]);
    }
}