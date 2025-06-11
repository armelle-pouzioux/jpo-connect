<?php
namespace Controller;

use Models\User;
use Config\Database;

class AuthController {
    private $userModel;

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->userModel = new User($db);
    }

    public function apiLogin() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_surname'] = $user['surname'];
            
            $token = bin2hex(random_bytes(32));
            $_SESSION['api_token'] = $token;
            
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'surname' => $user['surname'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'token' => $token
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        }
    }

    public function apiRegister() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $name = filter_var($input['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $surname = filter_var($input['surname'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (empty($name) || empty($surname) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            return;
        }

        if ($password !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userId = $this->userModel->create([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);

        if ($userId) {
            echo json_encode([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'user_id' => $userId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte']);
        }
    }

    public function apiLogout() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function apiMe() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            return;
        }

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'surname' => $_SESSION['user_surname'],
                'role' => $_SESSION['user_role']
            ]
        ]);
    }

    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public static function hasRole($role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($_SESSION['user_role'], $role);
        }
        
        return $_SESSION['user_role'] === $role;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentification requise']);
            exit;
        }
    }

    public static function requireRole($roles) {
        self::requireLogin();
        
        if (!self::hasRole($roles)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Droits insuffisants']);
            exit;
        }
    }
}
