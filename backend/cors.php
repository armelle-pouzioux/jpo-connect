<?php
// Liste des origines autorisées
$allowedOrigins = [
    "http://localhost:3000",
];

// Récupérer l'origine seulement si elle existe
$origin = $_SERVER['HTTP_ORIGIN'] ?? null;

// Autoriser seulement les domaines valides
if ($origin && in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    // Fallback pour certaines requêtes qui n'envoient pas Origin
    $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    if ($referer === 'localhost' && $_SERVER['SERVER_NAME'] === 'localhost') {
        header("Access-Control-Allow-Origin: http://localhost:3000");
    }
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");
header("Vary: Origin");

// Gestion des requêtes OPTIONS (préflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    header("Content-Length: 0");
    http_response_code(204);
    exit;
}