<?php
class Court
{
    public ?int $court_id;
    public string $court_name;
    public string $court_type;
    public string $location;
    public float $price_per_hour;
    public ?string $description;
    public ?string $image_url;
    public bool $is_active;

    public function __construct(array $data)
    {
        $this->court_id = $data['court_id'] ?? null;
        $this->court_name = $data['court_name'];
        $this->court_type = $data['court_type'];
        $this->location = $data['location'];
        $this->price_per_hour = (float)$data['price_per_hour'];
        $this->description = $data['description'] ?? null;
        $this->image_url = $data['image_url'] ?? null;
        $this->is_active = (bool)($data['is_active'] ?? true);
    }

    public function toArray(): array
    {
        return [
            'court_id' => $this->court_id,
            'court_name' => $this->court_name,
            'court_type' => $this->court_type,
            'location' => $this->location,
            'price_per_hour' => $this->price_per_hour,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active
        ];
    }
}
