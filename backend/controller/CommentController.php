<?php
namespace Controller;

use Models\Comment;
use Models\CommentResponse;  
use Models\JPO;
use Controller\AuthController;

class CommentController {
    private $commentModel;
    private $responseModel;
    private $jpoModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->commentModel = new Comment($db);
        $this->responseModel = new CommentResponse($db);
        $this->jpoModel = new JPO($db);
    }

    /**
     * Ajoute un commentaire à une JPO
     */
    public function addComment($jpoId) {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Validation des données
            if (empty($content)) {
                $_SESSION['error'] = "Le commentaire ne peut pas être vide";
                header('Location: /jpo/' . $jpoId);
                exit;
            }
            
            // Vérifier si la JPO existe
            $jpo = $this->jpoModel->findById($jpoId);
            
            if (!$jpo) {
                $_SESSION['error'] = "JPO non trouvée";
                header('Location: /jpo');
                exit;
            }
            
            // Ajouter le commentaire
            $commentId = $this->commentModel->create([
                'jpo_id' => $jpoId,
                'user_id' => $userId,
                'content' => $content,
                'status' => 'awaiting'
            ]);
            
            if ($commentId) {
                $_SESSION['success'] = "Votre commentaire a été ajouté et est en attente de modération";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'ajout du commentaire";
            }
            
            header('Location: /jpo/' . $jpoId);
            exit;
        }
    }

    /**
     * Affiche la liste des commentaires en attente de modération (admin)
     */
    public function moderationQueue() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        $comments = $this->commentModel->findByStatus('awaiting');
        
        require_once __DIR__ . '/../view/admin/comments/moderation_queue.php';
    }

    /**
     * Approuve un commentaire (admin)
     */
    public function approveComment($commentId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si le commentaire existe
        $comment = $this->commentModel->findById($commentId);
        
        if (!$comment) {
            $_SESSION['error'] = "Commentaire non trouvé";
            header('Location: /admin/comments/moderation');
            exit;
        }
        
        // Approuver le commentaire
        $success = $this->commentModel->updateStatus($commentId, 'approved');
        
        if ($success) {
            $_SESSION['success'] = "Commentaire approuvé avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'approbation du commentaire";
        }
        
        header('Location: /admin/comments/moderation');
        exit;
    }

    /**
     * Rejette un commentaire (admin)
     */
    public function rejectComment($commentId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si le commentaire existe
        $comment = $this->commentModel->findById($commentId);
        
        if (!$comment) {
            $_SESSION['error'] = "Commentaire non trouvé";
            header('Location: /admin/comments/moderation');
            exit;
        }
        
        // Rejeter le commentaire
        $success = $this->commentModel->updateStatus($commentId, 'denied');
        
        if ($success) {
            $_SESSION['success'] = "Commentaire rejeté avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors du rejet du commentaire";
        }
        
        header('Location: /admin/comments/moderation');
        exit;
    }

    /**
     * Supprime un commentaire (admin)
     */
    public function deleteComment($commentId) {
        AuthController::requireRole(['manager', 'director']);
        
        // Vérifier si le commentaire existe
        $comment = $this->commentModel->findById($commentId);
        
        if (!$comment) {
            $_SESSION['error'] = "Commentaire non trouvé";
            header('Location: /admin/comments/moderation');
            exit;
        }
        
        // Supprimer le commentaire
        $success = $this->commentModel->delete($commentId);
        
        if ($success) {
            $_SESSION['success'] = "Commentaire supprimé avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression du commentaire";
        }
        
        header('Location: /admin/comments/moderation');
        exit;
    }

    /**
     * Ajoute une réponse à un commentaire (admin)
     */
    public function addResponse($commentId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Validation des données
            if (empty($content)) {
                $_SESSION['error'] = "La réponse ne peut pas être vide";
                header('Location: /admin/comments/moderation');
                exit;
            }
            
            // Vérifier si le commentaire existe
            $comment = $this->commentModel->findById($commentId);
            
            if (!$comment) {
                $_SESSION['error'] = "Commentaire non trouvé";
                header('Location: /admin/comments/moderation');
                exit;
            }
            
            // Ajouter la réponse
            $responseId = $this->responseModel->create([
                'comment_id' => $commentId,
                'user_id' => $userId,
                'content' => $content
            ]);
            
            if ($responseId) {
                // Si le commentaire n'est pas encore approuvé, l'approuver automatiquement
                if ($comment['status'] === 'awaiting') {
                    $this->commentModel->updateStatus($commentId, 'approved');
                }
                
                $_SESSION['success'] = "Votre réponse a été ajoutée avec succès";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'ajout de la réponse";
            }
            
            header('Location: /admin/comments/moderation');
            exit;
        }
    }

    /**
     * Affiche tous les commentaires (admin)
     */
    public function allComments() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        $comments = $this->commentModel->findAll();
        
        require_once __DIR__ . '/../view/admin/comments/all.php';
    }
}