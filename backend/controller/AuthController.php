<?php
require_once __DIR__ . '/../Models/User.php';
use Models\User;
use Config\Database;

class AuthController {
    private $userModel;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->userModel = new User($db);
    }

    public function showLoginForm() {
        require_once __DIR__ . '/../view/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /login');
                exit;
            }

            $user = $this->userModel->findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                // Création de la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_surname'] = $user['surname'];
                
                // Redirection selon le rôle
                if (in_array($user['role'], ['employee', 'manager', 'director'])) {
                    header('Location: /dashboard');
                } else {
                    header('Location: /');
                }
                exit;
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect";
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Affiche la page d'inscription
     */
    public function showRegisterForm() {
        require_once __DIR__ . '/../view/auth/register.php';
    }

    /**
     * Traite l'inscription d'un utilisateur
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation des données
            if (empty($name) || empty($surname) || empty($email) || empty($password)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /register');
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                header('Location: /register');
                exit;
            }

            // Vérifier si l'email existe déjà
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
                header('Location: /register');
                exit;
            }

            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Création de l'utilisateur
            $userId = $this->userModel->create([
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'user'
            ]);

            if ($userId) {
                $_SESSION['success'] = "Votre compte a été créé avec succès";
                header('Location: /login');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la création du compte";
                header('Location: /register');
                exit;
            }
        }
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public static function hasRole($role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($_SESSION['user_role'], $role);
        }
        
        return $_SESSION['user_role'] === $role;
    }

    /**
     * Middleware pour restreindre l'accès aux utilisateurs connectés
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
            header('Location: /login');
            exit;
        }
    }

    /**
     * Middleware pour restreindre l'accès selon le rôle
     */
    public static function requireRole($roles) {
        self::requireLogin();
        
        if (!self::hasRole($roles)) {
            $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour accéder à cette page";
            header('Location: /');
            exit;
        }
    }
}