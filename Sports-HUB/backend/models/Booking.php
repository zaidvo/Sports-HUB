<?php
class Booking
{
    public ?int $booking_id;
    public int $user_id;
    public int $court_id;
    public int $slot_id;
    public ?string $booking_date;
    public ?float $amount_paid;
    public string $payment_status;
    public ?string $confirmation_message;
    public string $status;

    public function __construct(array $data)
    {
        $this->booking_id = $data['booking_id'] ?? null;
        $this->user_id = $data['user_id'];
        $this->court_id = $data['court_id'];
        $this->slot_id = $data['slot_id'];
        $this->booking_date = $data['booking_date'] ?? null;
        $this->amount_paid = $data['amount_paid'] ?? null;
        $this->payment_status = $data['payment_status'] ?? 'Pending';
        $this->confirmation_message = $data['confirmation_message'] ?? null;
        $this->status = $data['status'] ?? 'Confirmed';
    }

    public function toArray(): array
    {
        return [
            'booking_id' => $this->booking_id,
            'user_id' => $this->user_id,
            'court_id' => $this->court_id,
            'slot_id' => $this->slot_id,
            'booking_date' => $this->booking_date,
            'amount_paid' => $this->amount_paid,
            'payment_status' => $this->payment_status,
            'confirmation_message' => $this->confirmation_message,
            'status' => $this->status
        ];
    }
}