<?php
namespace App\Models;

class JPO {
    public ?int $id = null;
    public string $description;
    public string $date_jpo;
    public string $place;
    public int $capacity;
    public int $registered = 0;
    public string $status;
}
