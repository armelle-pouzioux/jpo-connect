<?php
namespace Controller;

use Models\Registration;
use Models\JPO;         
use Models\User;
use Controller\AuthController;
use Utils\Mailer;

class RegistrationController {
    private $registrationModel;
    private $jpoModel;
    private $userModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->registrationModel = new Registration($db);
        $this->jpoModel = new JPO($db);
        $this->userModel = new User($db);
    }

    /**
     * Inscrit un utilisateur à une JPO
     */
    public function register($jpoId) {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($jpoId);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /jpo');
            exit;
        }
        
        // Vérifier si la JPO est à venir
        if ($jpo['status'] !== 'upcoming') {
            $_SESSION['error'] = "Vous ne pouvez pas vous inscrire à une JPO terminée ou annulée";
            header('Location: /jpo/' . $jpoId);
            exit;
        }
        
        // Vérifier si la JPO n'est pas complète
        if ($jpo['registered'] >= $jpo['capacity']) {
            $_SESSION['error'] = "Cette JPO est complète";
            header('Location: /jpo/' . $jpoId);
            exit;
        }
        
        // Vérifier si l'utilisateur n'est pas déjà inscrit
        if ($this->registrationModel->isRegistered($userId, $jpoId)) {
            $_SESSION['error'] = "Vous êtes déjà inscrit à cette JPO";
            header('Location: /jpo/' . $jpoId);
            exit;
        }
        
        // Inscrire l'utilisateur
        $registrationId = $this->registrationModel->create([
            'user_id' => $userId,
            'jpo_id' => $jpoId,
            'presence' => 0
        ]);
        
        if ($registrationId) {
            // Incrémenter le nombre d'inscrits
            $this->jpoModel->incrementRegistered($jpoId);
            
            // Envoyer un email de confirmation
            $user = $this->userModel->findById($userId);
            
            require_once __DIR__ . '/../utils/Mailer.php';
            $mailer = new Mailer();
            $mailer->sendRegistrationConfirmation($user['email'], $jpo);
            
            $_SESSION['success'] = "Vous êtes inscrit à la JPO avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'inscription";
        }
        
        header('Location: /jpo/' . $jpoId);
        exit;
    }

    /**
     * Désinscrit un utilisateur d'une JPO
     */
    public function unregister($jpoId) {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($jpoId);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /jpo');
            exit;
        }
        
        // Vérifier si la JPO est à venir
        if ($jpo['status'] !== 'upcoming') {
            $_SESSION['error'] = "Vous ne pouvez pas vous désinscrire d'une JPO terminée ou annulée";
            header('Location: /jpo/' . $jpoId);
            exit;
        }
        
        // Vérifier si l'utilisateur est inscrit
        if (!$this->registrationModel->isRegistered($userId, $jpoId)) {
            $_SESSION['error'] = "Vous n'êtes pas inscrit à cette JPO";
            header('Location: /jpo/' . $jpoId);
            exit;
        }
        
        // Désinscrire l'utilisateur
        $success = $this->registrationModel->delete($userId, $jpoId);
        
        if ($success) {
            // Décrémenter le nombre d'inscrits
            $this->jpoModel->decrementRegistered($jpoId);
            
            $_SESSION['success'] = "Vous êtes désinscrit de la JPO avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la désinscription";
        }
        
        header('Location: /jpo/' . $jpoId);
        exit;
    }

    /**
     * Affiche les JPO auxquelles l'utilisateur est inscrit
     */
    public function myRegistrations() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les inscriptions de l'utilisateur
        $registrations = $this->registrationModel->findByUser($userId);
        
        require_once __DIR__ . '/../view/registration/my_registrations.php';
    }

    /**
     * Affiche la liste des inscrits à une JPO (admin)
     */
    public function jpoRegistrations($jpoId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($jpoId);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Récupérer les inscriptions pour cette JPO
        $registrations = $this->registrationModel->findByJpoWithUsers($jpoId);
        
        require_once __DIR__ . '/../view/admin/registration/jpo_registrations.php';
    }

    /**
     * Marque un utilisateur comme présent à une JPO (admin)
     */
    public function markPresent($registrationId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si l'inscription existe
        $registration = $this->registrationModel->findById($registrationId);
        
        if (!$registration) {
            $_SESSION['error'] = "Inscription non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Mettre à jour la présence
        $success = $this->registrationModel->updatePresence($registrationId, 1);
        
        if ($success) {
            $_SESSION['success'] = "Présence marquée avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de la présence";
        }
        
        header('Location: /admin/jpo/' . $registration['jpo_id'] . '/registrations');
        exit;
    }

    /**
     * Marque un utilisateur comme absent à une JPO (admin)
     */
    public function markAbsent($registrationId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si l'inscription existe
        $registration = $this->registrationModel->findById($registrationId);
        
        if (!$registration) {
            $_SESSION['error'] = "Inscription non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Mettre à jour la présence
        $success = $this->registrationModel->updatePresence($registrationId, 0);
        
        if ($success) {
            $_SESSION['success'] = "Absence marquée avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de la présence";
        }
        
        header('Location: /admin/jpo/' . $registration['jpo_id'] . '/registrations');
        exit;
    }

    /**
     * Envoie un rappel par email à tous les inscrits d'une JPO (admin)
     */
    public function sendReminders($jpoId) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($jpoId);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Vérifier si la JPO est à venir
        if ($jpo['status'] !== 'upcoming') {
            $_SESSION['error'] = "Vous ne pouvez pas envoyer de rappels pour une JPO terminée ou annulée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Récupérer les inscriptions pour cette JPO
        $registrations = $this->registrationModel->findByJpoWithUsers($jpoId);
        
        if (empty($registrations)) {
            $_SESSION['error'] = "Aucun inscrit pour cette JPO";
            header('Location: /admin/jpo/' . $jpoId . '/registrations');
            exit;
        }
        
        // Envoyer les rappels
        require_once __DIR__ . '/../utils/Mailer.php';
        $mailer = new Mailer();
        $sentCount = 0;
        
        foreach ($registrations as $registration) {
            if ($mailer->sendReminderEmail($registration['email'], $jpo)) {
                $sentCount++;
            }
        }
        
        if ($sentCount > 0) {
            $_SESSION['success'] = "Rappels envoyés avec succès à $sentCount inscrits";
        } else {
            $_SESSION['error'] = "Aucun rappel n'a pu être envoyé";
        }
        
        header('Location: /admin/jpo/' . $jpoId . '/registrations');
        exit;
    }
}