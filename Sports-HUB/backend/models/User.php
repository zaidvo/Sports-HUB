<?php
class User
{
    public ?int $user_id;
    public string $full_name;
    public string $phone_number;
    public ?string $email;
    public string $password_hash;
    public ?string $created_at;

    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->full_name = $data['full_name'];
        $this->phone_number = $data['phone_number'];
        $this->email = $data['email'] ?? null;
        $this->password_hash = $data['password_hash'];
        $this->created_at = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'created_at' => $this->created_at
            // Note: password_hash is excluded for security
        ];
    }
}
