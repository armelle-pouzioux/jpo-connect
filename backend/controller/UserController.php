<?
namespace Controller;

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/AuthController.php';

use Models\User;
use Config\Database;
use Controller\AuthController;

class UserController {
    private $userModel;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->userModel = new User($db);
    }

    /**
     * Affiche la liste des utilisateurs (admin)
     */
    public function index() {
        // Vérifier les droits d'accès
        AuthController::requireRole(['manager', 'director']);
        
        $users = $this->userModel->findAll();
        require_once __DIR__ . '/../view/admin/users/index.php';
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     */
    public function profile() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: /');
            exit;
        }
        
        require_once __DIR__ . '/../view/user/profile.php';
    }

    /**
     * Affiche le formulaire de modification du profil
     */
    public function editProfile() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: /');
            exit;
        }
        
        require_once __DIR__ . '/../view/user/edit_profile.php';
    }

    /**
     * Traite la modification du profil
     */
    public function updateProfile() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation des données
            if (empty($name) || empty($surname) || empty($email)) {
                $_SESSION['error'] = "Les champs nom, prénom et email sont obligatoires";
                header('Location: /profile/edit');
                exit;
            }
            
            $user = $this->userModel->findById($userId);
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $_SESSION['error'] = "Cet email est déjà utilisé par un autre compte";
                header('Location: /profile/edit');
                exit;
            }
            
            // Préparation des données à mettre à jour
            $userData = [
                'name' => $name,
                'surname' => $surname,
                'email' => $email
            ];
            
            // Si l'utilisateur souhaite changer son mot de passe
            if (!empty($currentPassword) && !empty($newPassword)) {
                // Vérifier l'ancien mot de passe
                if (!password_verify($currentPassword, $user['password'])) {
                    $_SESSION['error'] = "Le mot de passe actuel est incorrect";
                    header('Location: /profile/edit');
                    exit;
                }
                
                // Vérifier que les nouveaux mots de passe correspondent
                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = "Les nouveaux mots de passe ne correspondent pas";
                    header('Location: /profile/edit');
                    exit;
                }
                
                // Hasher le nouveau mot de passe
                $userData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            
            // Mettre à jour l'utilisateur
            $success = $this->userModel->update($userId, $userData);
            
            if ($success) {
                // Mettre à jour les données de session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_surname'] = $surname;
                
                $_SESSION['success'] = "Votre profil a été mis à jour avec succès";
                header('Location: /profile');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du profil";
                header('Location: /profile/edit');
                exit;
            }
        }
    }

    /**
     * Affiche le formulaire de création d'un utilisateur (admin)
     */
    public function create() {
        AuthController::requireRole(['director']);
        
        require_once __DIR__ . '/../view/admin/users/create.php';
    }

    /**
     * Traite la création d'un utilisateur (admin)
     */
    public function store() {
        AuthController::requireRole(['director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Validation des données
            if (empty($name) || empty($surname) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /admin/users/create');
                exit;
            }
            
            // Vérifier si l'email existe déjà
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
                header('Location: /admin/users/create');
                exit;
            }
            
            // Vérifier que le rôle est valide
            $validRoles = ['user', 'employee', 'manager', 'director'];
            if (!in_array($role, $validRoles)) {
                $_SESSION['error'] = "Le rôle sélectionné n'est pas valide";
                header('Location: /admin/users/create');
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
                'role' => $role
            ]);
            
            if ($userId) {
                $_SESSION['success'] = "L'utilisateur a été créé avec succès";
                header('Location: /admin/users');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la création de l'utilisateur";
                header('Location: /admin/users/create');
                exit;
            }
        }
    }

    /**
     * Affiche le formulaire d'édition d'un utilisateur (admin)
     */
    public function edit($id) {
        AuthController::requireRole(['director']);
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: /admin/users');
            exit;
        }
        
        require_once __DIR__ . '/../view/admin/users/edit.php';
    }

    /**
     * Traite la mise à jour d'un utilisateur (admin)
     */
    public function update($id) {
        AuthController::requireRole(['director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
            $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
            $newPassword = $_POST['new_password'] ?? '';
            
            // Validation des données
            if (empty($name) || empty($surname) || empty($email) || empty($role)) {
                $_SESSION['error'] = "Les champs nom, prénom, email et rôle sont obligatoires";
                header('Location: /admin/users/edit/' . $id);
                exit;
            }
            
            // Vérifier si l'utilisateur existe
            $user = $this->userModel->findById($id);
            if (!$user) {
                $_SESSION['error'] = "Utilisateur non trouvé";
                header('Location: /admin/users');
                exit;
            }
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                $_SESSION['error'] = "Cet email est déjà utilisé par un autre compte";
                header('Location: /admin/users/edit/' . $id);
                exit;
            }
            
            // Vérifier que le rôle est valide
            $validRoles = ['user', 'employee', 'manager', 'director'];
            if (!in_array($role, $validRoles)) {
                $_SESSION['error'] = "Le rôle sélectionné n'est pas valide";
                header('Location: /admin/users/edit/' . $id);
                exit;
            }
            
            // Préparation des données à mettre à jour
            $userData = [
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
                'role' => $role
            ];
            
            // Si un nouveau mot de passe est fourni
            if (!empty($newPassword)) {
                $userData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            
            // Mettre à jour l'utilisateur
            $success = $this->userModel->update($id, $userData);
            
            if ($success) {
                $_SESSION['success'] = "L'utilisateur a été mis à jour avec succès";
                header('Location: /admin/users');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de l'utilisateur";
                header('Location: /admin/users/edit/' . $id);
                exit;
            }
        }
    }

    /**
     * Supprime un utilisateur (admin)
     */
    public function delete($id) {
        AuthController::requireRole(['director']);
        
        // Vérifier si l'utilisateur existe
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: /admin/users');
            exit;
        }
        
        // Empêcher la suppression de son propre compte
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte";
            header('Location: /admin/users');
            exit;
        }
        
        // Supprimer l'utilisateur
        $success = $this->userModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "L'utilisateur a été supprimé avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression de l'utilisateur";
        }
        
        header('Location: /admin/users');
        exit;
    }
}