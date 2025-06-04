<?php
// models/Registration.php
namespace Models;

use PDO;

class Registration {
    private $conn;
    private $table = 'registration';

    public $id;
    public $user_id;
    public $jpo_id;
    public $date_registration;
    public $presence;

    public function __construct($db) {
        $this->conn = $db;
        $this->table = 'registrations'; // or your actual table name
    }

    // S'inscrire à un JPO
    public function register() {
        // Vérifier si déjà inscrit
        if ($this->isAlreadyRegistered()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (user_id, jpo_id) 
                  VALUES (:user_id, :jpo_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':jpo_id', $this->jpo_id);
        
        if ($stmt->execute()) {
            // Mettre à jour le compteur
            $jpo = new JPO($this->conn);
            $jpo->id = $this->jpo_id;
            $jpo->updateRegisteredCount();
            return true;
        }
        return false;
    }

    // Se désinscrire d'un JPO
    public function unregister() {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE user_id = :user_id AND jpo_id = :jpo_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':jpo_id', $this->jpo_id);
        
        if ($stmt->execute()) {
            // Mettre à jour le compteur
            $jpo = new JPO($this->conn);
            $jpo->id = $this->jpo_id;
            $jpo->updateRegisteredCount();
            return true;
        }
        return false;
    }

    // Vérifier si déjà inscrit
    public function isAlreadyRegistered() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE user_id = :user_id AND jpo_id = :jpo_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':jpo_id', $this->jpo_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function isRegistered($userId, $jpoId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE user_id = :user_id AND jpo_id = :jpo_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':jpo_id', $jpoId);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Récupérer les inscriptions d'un utilisateur
    public function getUserRegistrations($user_id) {
        $query = "SELECT r.*, j.description, j.date_jpo, j.place 
                  FROM " . $this->table . " r
                  JOIN jpo j ON r.jpo_id = j.id
                  WHERE r.user_id = :user_id
                  ORDER BY j.date_jpo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Marquer la présence
    public function markPresence($registration_id, $present = true) {
        $query = "UPDATE " . $this->table . " 
                  SET presence = :presence 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $registration_id);
        $stmt->bindParam(':presence', $present ? 1 : 0);
        
        return $stmt->execute();
    }

    // Trouver par JPO
    public function findByJpo($jpo_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE jpo_id = :jpo_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jpo_id', $jpo_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByJpoWithUsers($jpoId) {
        $query = "SELECT r.*, u.name, u.email
                  FROM " . $this->table . " r
                  JOIN user u ON r.user_id = u.id
                  WHERE r.jpo_id = :jpo_id
                  ORDER BY u.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jpo_id', $jpoId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (user_id, jpo_id, presence) VALUES (:user_id, :jpo_id, :presence)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':jpo_id', $data['jpo_id']);
        $stmt->bindParam(':presence', $data['presence']);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function delete($userId, $jpoId) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id AND jpo_id = :jpo_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':jpo_id', $jpoId);
        return $stmt->execute();
    }

    // Trouver par utilisateur
    public function findByUser($userId) {
        $query = "SELECT r.*, j.description, j.date_jpo, j.place
                  FROM " . $this->table . " r
                  JOIN jpo j ON r.jpo_id = j.id
                  WHERE r.user_id = :user_id
                  ORDER BY j.date_jpo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($registrationId) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $registrationId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePresence($registrationId, $presence) {
        $query = "UPDATE " . $this->table . " SET presence = :presence WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':presence', $presence, PDO::PARAM_INT);
        $stmt->bindParam(':id', $registrationId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findLatest($limit = 5) {
        $query = "SELECT r.*, u.name, u.surname, j.description, j.date_jpo
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  JOIN jpo j ON r.jpo_id = j.id
                  ORDER BY r.id DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceRate() {
        // Total registrations
        $queryTotal = "SELECT COUNT(*) FROM " . $this->table;
        $stmtTotal = $this->conn->prepare($queryTotal);
        $stmtTotal->execute();
        $total = (int)$stmtTotal->fetchColumn();

        // Registrations marked as present
        $queryPresent = "SELECT COUNT(*) FROM " . $this->table . " WHERE presence = 1";
        $stmtPresent = $this->conn->prepare($queryPresent);
        $stmtPresent->execute();
        $present = (int)$stmtPresent->fetchColumn();

        if ($total === 0) {
            return 0;
        }
        return round(($present / $total) * 100, 2); // returns percentage
    }

    public function findAllWithDetails() {
        $query = "SELECT r.id, u.name AS utilisateur, u.email, j.description AS jpo, j.date_jpo AS date, j.place AS lieu, r.created_at AS date_inscription, r.presence
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  JOIN jpo j ON r.jpo_id = j.id
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

