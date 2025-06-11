<?php
// models/JPO.php
namespace Models;

use PDO;

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
        $this->table ='jpo';
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
            return $row;
        }
        return false;
    }

    // Récupérer des JPO par statut
    public function findByStatus($status, $limit = null) {
        $query = "SELECT * FROM " . $this->table . " WHERE status = :status ORDER BY id DESC";
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // Retourne la liste des lieux possibles
    public function getPlaces() {
        return ['Marseille', 'Paris', 'Cannes', 'Martigues', 'Toulon', 'Brignoles'];
    }

    // Met à jour le nombre d'inscrits
    public function updateRegisteredCount()
    {
        // Compte les inscriptions pour ce JPO
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM registrations WHERE jpo_id = :jpo_id');
        $stmt->execute(['jpo_id' => $this->id]);
        $count = $stmt->fetchColumn();

        // Met à jour le compteur d'inscrits dans la table JPO
        $updateStmt = $this->conn->prepare('UPDATE jpo SET registered = :count WHERE id = :id');
        $updateStmt->execute(['count' => $count, 'id' => $this->id]);
    }

    public function incrementRegistered($jpoId) {
        $query = "UPDATE jpo SET registered = registered + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $jpoId);
        return $stmt->execute();
    }

    public function decrementRegistered($jpoId) {
        $query = "UPDATE jpo SET registered = registered - 1 WHERE id = :id AND registered > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $jpoId);
        return $stmt->execute();
    }

    // Compte le nombre total de JPO
    public function count() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function countByStatus($status) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getStatsByPlace() {
        $query = "SELECT place, COUNT(*) as total_jpo
                  FROM " . $this->table . "
                  GROUP BY place
                  ORDER BY total_jpo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsByMonth() {
        $query = "SELECT DATE_FORMAT(date_jpo, '%Y-%m') AS month, COUNT(*) as total_jpo
                  FROM " . $this->table . "
                  GROUP BY month
                  ORDER BY month DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

