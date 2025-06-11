// backend/public/test-auth.php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../cors.php';

header('Content-Type: application/json');

try {
    echo json_encode(['step' => 'Début du test']);
    
    // Test 1: Instancier le contrôleur
    $authController = new Controller\AuthController();
    echo json_encode(['step' => 'AuthController instancié']);
    
    // Test 2: Simuler une requête register
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $testData = [
        'name' => 'Test',
        'surname' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123'
    ];
    
    // Simuler le contenu JSON
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($testData);
    
    // Appeler la méthode register
    echo json_encode(['step' => 'Avant apiRegister']);
    $authController->apiRegister();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>