<?php
// models/JPO.php
namespace Models;
class JPO {
    private $conn;
    private $table = 'jpo';

    public $id;
    public $description;
    public $date_jpo;
    public $place;
    public $capacity;
    public $registered;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un JPO
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (description, date_jpo, place, capacity, status) 
                  VALUES (:description, :date_jpo, :place, :capacity, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':date_jpo', $this->date_jpo);
        $stmt->bindParam(':place', $this->place);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }

    // Récupérer tous les JPO
    public function getAll($filters = []) {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
        
        // Filtres optionnels
        if (!empty($filters['place'])) {
            $query .= " AND place = :place";
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND date_jpo >= :date_from";
        }
        
        $query .= " ORDER BY date_jpo ASC";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind des filtres
        if (!empty($filters['place'])) {
            $stmt->bindParam(':place', $filters['place']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(':date_from', $filters['date_from']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Récupérer un JPO par ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->id = $row['id'];
            $this->description = $row['description'];
            $this->date_jpo = $row['date_jpo'];
            $this->place = $row['place'];
            $this->capacity = $row['capacity'];
            $this->registered = $row['registered'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Mettre à jour un JPO
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET description = :description, date_jpo = :date_jpo, 
                      place = :place, capacity = :capacity, status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':date_jpo', $this->date_jpo);
        $stmt->bindParam(':place', $this->place);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }

    // Supprimer un JPO
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Vérifier si des places sont disponibles
    public function hasAvailableSpots() {
        return $this->registered < $this->capacity;
    }

    // Mettre à jour le nombre d'inscrits
    public function updateRegisteredCount() {
        $query = "UPDATE " . $this->table . " 
                  SET registered = (
                      SELECT COUNT(*) FROM registration 
                      WHERE jpo_id = :id
                  ) 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}


