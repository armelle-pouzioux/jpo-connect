// backend/public/test-class.php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

try {
    // Test 1: Vérifier si la classe existe
    if (class_exists('Controller\\AuthController')) {
        echo json_encode(['step' => 1, 'message' => 'Classe AuthController trouvée']);
    } else {
        echo json_encode(['step' => 1, 'error' => 'Classe AuthController NON trouvée']);
        exit;
    }
    
    // Test 2: Instancier la classe
    $authController = new Controller\AuthController();
    echo json_encode(['step' => 2, 'message' => 'AuthController instancié']);
    
    // Test 3: Vérifier les méthodes disponibles
    $methods = get_class_methods($authController);
    echo json_encode(['step' => 3, 'methods' => $methods]);
    
    // Test 4: Vérifier si apiRegister existe
    if (method_exists($authController, 'apiRegister')) {
        echo json_encode(['step' => 4, 'message' => 'Méthode apiRegister trouvée']);
    } else {
        echo json_encode(['step' => 4, 'error' => 'Méthode apiRegister NON trouvée']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>