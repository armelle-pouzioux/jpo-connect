// backend/public/test-user-model.php
<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Config\Database;
use Models\User;

header('Content-Type: application/json');

try {
    // Test de la connexion
    $db = Database::getInstance()->getConnection();
    echo json_encode(['step' => 1, 'message' => 'Connexion DB OK']);
    
    // Test du modèle User
    $userModel = new User($db);
    echo json_encode(['step' => 2, 'message' => 'Modèle User créé']);
    
    // Test findByEmail avec un email qui n'existe pas
    $user = $userModel->findByEmail('test@nonexistant.com');
    echo json_encode(['step' => 3, 'message' => 'findByEmail testé: ' . ($user ? 'trouvé' : 'non trouvé')]);
    
    // Test avec l'admin par défaut
    $admin = $userModel->findByEmail('admin@laplateforme.io');
    echo json_encode(['step' => 4, 'message' => 'Admin trouvé: ' . ($admin ? 'oui' : 'non')]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Modèle User fonctionne correctement'
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