<?php
// models/User.php
namespace Models;
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

    // Créer un utilisateur
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, surname, email, password, role) 
                  VALUES (:name, :surname, :email, :password, :role)";

        $stmt = $this->conn->prepare($query);

        // Hash du mot de passe
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname', $this->surname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $this->role);

        return $stmt->execute();
    }

    // Vérifier les identifiants
    public function login($email, $password) {
        $query = "SELECT id, name, surname, email, password, role 
                  FROM " . $this->table . " 
                  WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
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
    }

    // Vérifier les permissions
    public function hasPermission($required_role) {
        $hierarchy = ['user' => 1, 'employee' => 2, 'manager' => 3, 'director' => 4];
        return $hierarchy[$this->role] >= $hierarchy[$required_role];
    }

    // Récupérer un utilisateur par ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->surname = $row['surname'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->date_creation = $row['date_creation'];
            return true;
        }
        return false;
    }
}

