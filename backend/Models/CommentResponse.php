<?php
// models/CommentResponse.php
namespace Models;
class CommentResponse {
    private $conn;
    private $table = 'comments_responses';

    public $id;
    public $comment_id;
    public $user_id;
    public $content;
    public $response_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une réponse
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (comment_id, user_id, content) 
                  VALUES (:comment_id, :user_id, :content)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':comment_id', $this->comment_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':content', $this->content);
        
        return $stmt->execute();
    }

    // Récupérer les réponses d'un commentaire
    public function getByComment($comment_id) {
        $query = "SELECT cr.*, u.name, u.surname, u.role 
                  FROM " . $this->table . " cr
                  JOIN users u ON cr.user_id = u.id
                  WHERE cr.comment_id = :comment_id
                  ORDER BY cr.response_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':comment_id', $comment_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

