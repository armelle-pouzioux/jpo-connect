<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gestionnaire d'exceptions
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});

require_once __DIR__ . '/../error-handler.php';
require_once __DIR__ . '/../cors.php';
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

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

$path = trim($path, '/');

if (empty($path)) {
    $path = 'home';
}

$segments = explode('/', $path);
$controller = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$param1 = $segments[2] ?? null;
$param2 = $segments[3] ?? null;


switch ($controller) {
    
case 'auth':
    $authController = new AuthController();
    switch ($action) {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->apiLogin();
            }
            break;
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->apiRegister();
            }
            break;
        case 'logout':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->apiLogout();
            }
            break;
        case 'me':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $authController->apiMe();
            }
            break;
    }
    break;
    
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (empty($action) || $action === 'index') {
            $jpoController->index(); // GET /jpo
        } elseif (is_numeric($action)) {
            $jpoController->show($action); // GET /jpo/2
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'create') {
            $jpoController->store(); // POST /jpo/create
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if ($action === 'update' && is_numeric($param1)) {
            $jpoController->update($param1); // PUT /jpo/update/2
        } elseif ($action === 'finish' && is_numeric($param1)) {
            $jpoController->markAsFinished($param1); // PUT /jpo/finish/2
        } elseif ($action === 'cancel' && is_numeric($param1)) {
            $jpoController->markAsCanceled($param1); // PUT /jpo/cancel/2
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if ($action === 'delete' && is_numeric($param1)) {
            $jpoController->delete($param1); // DELETE /jpo/delete/2
        }
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
            // admin/jpo/*
            case 'jpo':
                $jpoController = new JpoController($db);
                if ($param1 === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $jpoController->store(); // POST /admin/jpo/create
                } elseif ($param1 === 'edit' && is_numeric($param2) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $jpoController->update($param2); // PUT /admin/jpo/edit/2
                } elseif ($param1 === 'delete' && is_numeric($param2) && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    $jpoController->delete($param2); // DELETE /admin/jpo/delete/2
                } elseif ($param1 === 'finish' && is_numeric($param2) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $jpoController->markAsFinished($param2); // PUT /admin/jpo/finish/2
                } elseif ($param1 === 'cancel' && is_numeric($param2) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $jpoController->markAsCanceled($param2); // PUT /admin/jpo/cancel/2
                } elseif (is_numeric($param1) && $param2 === 'registrations') {
                    $registrationController = new RegistrationController($db);
                    $registrationController->jpoRegistrations($param1); // GET /admin/jpo/3/registrations
                } else {
                    $jpoController->adminIndex(); // GET /admin/jpo
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

