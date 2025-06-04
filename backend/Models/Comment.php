<?php
// models/Comment.php
namespace Models;
class Comment {
    private $conn;
    private $table = 'comments';

    public $id;
    public $jpo_id;
    public $user_id;
    public $content;
    public $date_comment;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un commentaire
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (jpo_id, user_id, content, status) 
                  VALUES (:jpo_id, :user_id, :content, 'awaiting')";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':jpo_id', $this->jpo_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':content', $this->content);
        
        return $stmt->execute();
    }

    // Récupérer les commentaires approuvés d'un JPO
    public function getApprovedByJPO($jpo_id) {
        $query = "SELECT c.*, u.name, u.surname 
                  FROM " . $this->table . " c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.jpo_id = :jpo_id AND c.status = 'approved'
                  ORDER BY c.date_comment DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jpo_id', $jpo_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Récupérer tous les commentaires en attente (pour modération)
    public function getPendingComments() {
        $query = "SELECT c.*, u.name, u.surname, j.description as jpo_description 
                  FROM " . $this->table . " c
                  JOIN users u ON c.user_id = u.id
                  JOIN jpo j ON c.jpo_id = j.id
                  WHERE c.status = 'awaiting'
                  ORDER BY c.date_comment DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Modérer un commentaire
    public function moderate($comment_id, $status) {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $comment_id);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    // Supprimer un commentaire
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}

