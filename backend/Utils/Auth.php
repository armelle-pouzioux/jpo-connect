<?php
// utils/Auth.php
namespace Utils;
class Auth {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user) {
        self::startSession();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_name'] = $user->name . ' ' . $user->surname;
    }

    public static function logout() {
        self::startSession();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUser() {
        self::startSession();
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['user_role'],
                'name' => $_SESSION['user_name']
            ];
        }
        return null;
    }

    public static function hasPermission($required_role) {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        $hierarchy = ['user' => 1, 'employee' => 2, 'manager' => 3, 'director' => 4];
        return $hierarchy[$user['role']] >= $hierarchy[$required_role];
    }
}
?>