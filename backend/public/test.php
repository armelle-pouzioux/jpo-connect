// backend/public/test.php
<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Le serveur PHP fonctionne',
    'uri' => $_SERVER['REQUEST_URI'],
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>