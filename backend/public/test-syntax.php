// backend/public/test-syntax.php
<?php
header('Content-Type: application/json');

// Test de syntaxe du fichier AuthController
$file = __DIR__ . '/../Controller/AuthController.php';

if (!file_exists($file)) {
    echo json_encode(['error' => 'Fichier AuthController.php non trouvé']);
    exit;
}

// Vérifier la syntaxe PHP
$output = [];
$return_var = 0;
exec("php -l \"$file\" 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo json_encode(['syntax' => 'OK', 'output' => $output]);
} else {
    echo json_encode(['syntax' => 'ERROR', 'output' => $output]);
}

// Essayer d'inclure le fichier
try {
    require_once $file;
    echo json_encode(['include' => 'OK']);
} catch (Exception $e) {
    echo json_encode(['include' => 'ERROR', 'message' => $e->getMessage()]);
}
?>