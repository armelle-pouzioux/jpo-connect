<?php
namespace Models;

use PDO;
use PDOException;

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $surname;
    public $email;
    public $password;
    public $role;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create(array $data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (name, surname, email, password, role, is_active, email_verified) 
                      VALUES (:name, :surname, :email, :password, :role, 1, 0)";

            $stmt = $this->conn->prepare($query);

            // ✅ Le mot de passe est déjà hashé dans le contrôleur
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':surname', $data['surname']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':role', $data['role']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }

    // Get user by email
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur findByEmail: " . $e->getMessage());
            return false;
        }
    }

    // Check user login
    public function login($email, $password) {
        try {
            $query = "SELECT id, name, surname, email, password, role 
                      FROM " . $this->table . " 
                      WHERE email = :email AND is_active = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    $this->id = $row['id'];
                    $this->name = $row['name'];
                    $this->surname = $row['surname'];
                    $this->email = $row['email'];
                    $this->role = $row['role'];
                    return true;
                }
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur login: " . $e->getMessage());
            return false;
        }
    }

    // Get user by ID
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->surname = $row['surname'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                $this->date_creation = $row['date_creation'];
                return $row; // ✅ Retourner les données
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur findById: " . $e->getMessage());
            return false;
        }
    }

    // Get all users
    public function findAll() {
        try {
            $query = "SELECT id, name, surname, email, role, date_creation, is_active 
                      FROM " . $this->table . " 
                      ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur findAll: " . $e->getMessage());
            return [];
        }
    }

    // Update user
    public function update($id, array $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $sql = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }

    // Delete user (soft delete)
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }

    // Check if user has permission based on role
    public function hasPermission($required_role) {
        $hierarchy = ['user' => 1, 'employee' => 2, 'manager' => 3, 'director' => 4];
        return isset($hierarchy[$this->role]) && 
               isset($hierarchy[$required_role]) && 
               $hierarchy[$this->role] >= $hierarchy[$required_role];
    }

    // Count users
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erreur count: " . $e->getMessage());
            return 0;
        }
    }

    // Count users by role
    public function countByRole() {
        try {
            $query = "SELECT role, COUNT(*) as total 
                      FROM " . $this->table . " 
                      WHERE is_active = 1 
                      GROUP BY role";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur countByRole: " . $e->getMessage());
            return [];
        }
    }
}
?>