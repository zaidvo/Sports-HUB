<?php
class AvailableSlot
{
    public ?int $slot_id;
    public int $court_id;
    public string $date;
    public string $start_time;
    public string $end_time;
    public bool $is_booked;

    public function __construct(array $data)
    {
        $this->slot_id = $data['slot_id'] ?? null;
        $this->court_id = $data['court_id'];
        $this->date = $data['date'];
        $this->start_time = $data['start_time'];
        $this->end_time = $data['end_time'];
        $this->is_booked = $data['is_booked'] ?? false;
    }

    public function toArray(): array
    {
        return [
            'slot_id' => $this->slot_id,
            'court_id' => $this->court_id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_booked' => $this->is_booked
        ];
    }
}