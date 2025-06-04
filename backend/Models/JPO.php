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
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (description, date_jpo, place, capacity, registered, status)
                  VALUES (:description, :date_jpo, :place, :capacity, :registered, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':date_jpo', $data['date_jpo']);
        $stmt->bindParam(':place', $data['place']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':registered', $data['registered']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Récupérer tous les JPO
    public function findAll($filters = []) {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
        $params = [];
        
        // Filtres optionnels
        if (!empty($filters['place'])) {
            $query .= " AND place = :place";
            $params[':place'] = $filters['place'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        $query .= " ORDER BY date_jpo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
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
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET description = :description, date_jpo = :date_jpo, 
                          place = :place, capacity = :capacity, status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':date_jpo', $data['date_jpo']);
        $stmt->bindParam(':place', $data['place']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Supprimer un JPO
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getPlaces() {
        // Retourne la liste des lieux possibles (en dur ou depuis la base)
        return ['Marseille', 'Paris', 'Cannes', 'Martigues', 'Toulon', 'Brignoles'];
    }
}


