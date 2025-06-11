// backend/public/diagnostic.php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$tests = [];

// Test 1: PHP fonctionne
$tests['php'] = [
    'status' => 'OK',
    'version' => phpversion(),
    'time' => date('Y-m-d H:i:s')
];

// Test 2: Extensions PHP
$tests['extensions'] = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'json' => extension_loaded('json')
];

// Test 3: Connexion MySQL
try {
    $pdo = new PDO('mysql:host=localhost;dbname=jpo_connect', 'root', '');
    $tests['mysql'] = [
        'status' => 'OK',
        'connection' => true
    ];
    
    // Test 4: Table users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    $tests['database'] = [
        'status' => 'OK',
        'users_count' => $result['count']
    ];
    
} catch (Exception $e) {
    $tests['mysql'] = [
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ];
}

echo json_encode($tests, JSON_PRETTY_PRINT);
?>