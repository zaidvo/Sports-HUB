<?php
class Admin
{
    public ?int $admin_id;
    public string $username;
    public string $password_hash;
    public ?string $created_at;

    public function __construct(array $data)
    {
        $this->admin_id = $data['admin_id'] ?? null;
        $this->username = $data['username'];
        $this->password_hash = $data['password_hash'];
        $this->created_at = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'admin_id' => $this->admin_id,
            'username' => $this->username,
            'created_at' => $this->created_at
            // Note: password_hash is excluded for security
        ];
    }
}
