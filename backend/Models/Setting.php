<?php
require_once __DIR__ . '/../Config/Database.php';

namespace Models;
use PDO;
use Config\Database;

class Setting {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['value'] : $default;
    }

    public function set($key, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        
        return $stmt->execute([$key, $value]);
    }


    public function getAll() {
        $stmt = $this->db->query("SELECT setting_key, value FROM settings");
        $settings = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['value'];
        }
        
        return $settings;
    }

    /**
     * Supprime un paramètre
     */
    public function delete($key) {
        $stmt = $this->db->prepare("DELETE FROM settings WHERE setting_key = ?");
        return $stmt->execute([$key]);
    }

    /**
     * Vérifie si un paramètre existe
     */
    public function exists($key) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() > 0;
    }
}