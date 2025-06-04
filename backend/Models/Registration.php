<?php
// models/Registration.php
namespace Models;
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
}

