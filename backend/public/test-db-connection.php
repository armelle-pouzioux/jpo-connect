// backend/public/test-db-connection.php
<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Config\Database;

header('Content-Type: application/json');

try {
    echo json_encode(['step' => 1, 'message' => 'Début du test']);
    
    // Test 1: Instanciation de la classe
    $database = Database::getInstance();
    echo json_encode(['step' => 2, 'message' => 'Instance Database créée']);
    
    // Test 2: Test de connexion
    $isConnected = $database->testConnection();
    echo json_encode(['step' => 3, 'message' => 'Test connexion: ' . ($isConnected ? 'OK' : 'ÉCHEC')]);
    
    // Test 3: Récupération de la connexion
    $conn = $database->getConnection();
    echo json_encode(['step' => 4, 'message' => 'Connexion récupérée']);
    
    // Test 4: Requête simple
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tous les tests réussis',
        'users_count' => $result['count']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>