<?php
namespace Controller;

use Models\Setting;
use Controller\AuthController;

class SettingsController {
    private $settingModel;

    public function __construct() {
        $this->settingModel = new Setting();
    }

    /**
     * Affiche la page des paramètres
     */
    public function index() {
        AuthController::requireRole(['director']);
        
        $settings = $this->settingModel->getAll();
        
        require_once __DIR__ . '/../view/admin/settings/index.php';
    }

    /**
     * Met à jour les paramètres
     */
    public function update() {
        AuthController::requireRole(['director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
            $reminderDays = filter_input(INPUT_POST, 'reminder_days', FILTER_VALIDATE_INT);
            $siteTitle = filter_input(INPUT_POST, 'site_title', FILTER_SANITIZE_SPECIAL_CHARS);
            $contactEmail = filter_input(INPUT_POST, 'contact_email', FILTER_SANITIZE_EMAIL);
            
            // Validation des données
            if ($reminderDays === false || $reminderDays < 0) {
                $_SESSION['error'] = "Le nombre de jours pour les rappels doit être un nombre positif";
                header('Location: /admin/settings');
                exit;
            }
            
            if (empty($siteTitle) || empty($contactEmail)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /admin/settings');
                exit;
            }
            
            // Mettre à jour les paramètres
            $settings = [
                'email_notifications' => $emailNotifications,
                'reminder_days' => $reminderDays,
                'site_title' => $siteTitle,
                'contact_email' => $contactEmail
            ];
            
            $success = true;
            
            foreach ($settings as $key => $value) {
                if (!$this->settingModel->set($key, $value)) {
                    $success = false;
                }
            }
            
            if ($success) {
                $_SESSION['success'] = "Les paramètres ont été mis à jour avec succès";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour des paramètres";
            }
            
            header('Location: /admin/settings');
            exit;
        }
    }

    /**
     * Récupère un paramètre spécifique
     */
    public static function get($key, $default = null) {
        $settingModel = new Setting();
        return $settingModel->get($key, $default);
    }
}