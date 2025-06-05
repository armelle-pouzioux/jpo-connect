<?php
// Initialiser la session
session_start();

// Charger les contrôleurs
require_once __DIR__ . '/../../vendor/autoload.php';

use Controller\AuthController;
use Controller\UserController;      
use Controller\JpoController;
use Controller\RegistrationController;
use Controller\CommentController;
use Controller\DashboardController; 
use Controller\SettingsController;
use Config\Database;

$db = Database::getInstance()->getConnection();



// Récupérer l'URL demandée
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Supprimer les slashes au début et à la fin
$path = trim($path, '/');

// Si le chemin est vide, rediriger vers la page d'accueil
if (empty($path)) {
    $path = 'home';
}

// Diviser le chemin en segments
$segments = explode('/', $path);
$controller = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$param1 = $segments[2] ?? null;
$param2 = $segments[3] ?? null;

// Router les requêtes vers les contrôleurs appropriés
switch ($controller) {
    // Routes d'authentification
    case 'login':
        $authController = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->login();
        } else {
            $authController->showLoginForm();
        }
        break;
        
    case 'register':
        $authController = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->register();
        } else {
            $authController->showRegisterForm();
        }
        break;
        
    case 'logout':
        $authController = new AuthController();
        $authController->logout();
        break;
    
    // Routes utilisateur
    case 'profile':
        $userController = new UserController();
        if ($action === 'edit') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userController->updateProfile();
            } else {
                $userController->editProfile();
            }
        } else {
            $userController->profile();
        }
        break;
    
    // Routes JPO
    case 'jpo':
        $jpoController = new JpoController($db);
        if (empty($action) || $action === 'index') {
            $jpoController->index();
        } elseif (is_numeric($action)) {
            $jpoController->show($action);
        }
        break;
    
    // Routes d'inscription
    case 'registration':
        $registrationController = new RegistrationController($db);
        if ($action === 'register' && is_numeric($param1)) {
            $registrationController->register($param1);
        } elseif ($action === 'unregister' && is_numeric($param1)) {
            $registrationController->unregister($param1);
        } elseif ($action === 'my') {
            $registrationController->myRegistrations();
        }
        break;
    
    // Routes de commentaires
    case 'comment':
        $commentController = new CommentController($db);
        if ($action === 'add' && is_numeric($param1)) {
            $commentController->addComment($param1);
        }
        break;
    
    // Routes d'administration
    case 'admin':
        switch ($action) {
            // Dashboard
            case 'dashboard':
                $dashboardController = new DashboardController($db);
                if ($param1 === 'statistics') {
                    $dashboardController->statistics();
                } elseif ($param1 === 'export' && !empty($param2)) {
                    $dashboardController->exportData($param2);
                } else {
                    $dashboardController->index();
                }
                break;
                
            // Utilisateurs
            case 'users':
                $userController = new UserController();
                if ($param1 === 'create') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $userController->store();
                    } else {
                        $userController->create();
                    }
                } elseif ($param1 === 'edit' && is_numeric($param2)) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $userController->update($param2);
                    } else {
                        $userController->edit($param2);
                    }
                } elseif ($param1 === 'delete' && is_numeric($param2)) {
                    $userController->delete($param2);
                } else {
                    $userController->index();
                }
                break;
                
            // JPO
            case 'jpo':
                $jpoController = new JpoController($db);
                if ($param1 === 'create') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $jpoController->store();
                    } else {
                        $jpoController->create();
                    }
                } elseif ($param1 === 'edit' && is_numeric($param2)) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $jpoController->update($param2);
                    } else {
                        $jpoController->edit($param2);
                    }
                } elseif ($param1 === 'delete' && is_numeric($param2)) {
                    $jpoController->delete($param2);
                } elseif ($param1 === 'finish' && is_numeric($param2)) {
                    $jpoController->markAsFinished($param2);
                } elseif ($param1 === 'cancel' && is_numeric($param2)) {
                    $jpoController->markAsCanceled($param2);
                } elseif (is_numeric($param1) && $param2 === 'registrations') {
                    $registrationController = new RegistrationController($db);
                    $registrationController->jpoRegistrations($param1);
                } else {
                    $jpoController->adminIndex();
                }
                break;
                
            // Commentaires
            case 'comments':
                $commentController = new CommentController($db);
                if ($param1 === 'moderation') {
                    $commentController->moderationQueue();
                } elseif ($param1 === 'approve' && is_numeric($param2)) {
                    $commentController->approveComment($param2);
                } elseif ($param1 === 'reject' && is_numeric($param2)) {
                    $commentController->rejectComment($param2);
                } elseif ($param1 === 'delete' && is_numeric($param2)) {
                    $commentController->deleteComment($param2);
                } elseif ($param1 === 'respond' && is_numeric($param2)) {
                    $commentController->addResponse($param2);
                } else {
                    $commentController->allComments();
                }
                break;
                
            // Inscriptions
            case 'registrations':
                $registrationController = new RegistrationController($db);
                if ($param1 === 'present' && is_numeric($param2)) {
                    $registrationController->markPresent($param2);
                } elseif ($param1 === 'absent' && is_numeric($param2)) {
                    $registrationController->markAbsent($param2);
                } elseif ($param1 === 'remind' && is_numeric($param2)) {
                    $registrationController->sendReminders($param2);
                }
                break;
                
            // Paramètres
            case 'settings':
                $settingsController = new SettingsController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $settingsController->update();
                } else {
                    $settingsController->index();
                }
                break;
                
            // Page d'accueil admin
            default:
                $dashboardController = new DashboardController($db);
                $dashboardController->index();
                break;
        }
        break;
    
    // Page d'accueil
    case 'home':
    default:
        // Rediriger vers la liste des JPO
        $jpoController = new JpoController($db);
        $jpoController->index();
        break;
}

