<?php
// backend/routes/router.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controller/JpoController.php';
require_once __DIR__ . '/../controller/InscriptionController.php';
require_once __DIR__ . '/../controller/CommentController.php';
require_once __DIR__ . '/../controller/AdminController.php';
// Ajoute d'autres require_once ici si tu as d’autres contrôleurs

// En-têtes JSON + CORS pour accès depuis React (localhost:5173)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Gérer les requêtes préalables OPTIONS (indispensable pour React)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Instanciation de la connexion BDD
$database = new Database();
$db = $database->getConnection();

// Récupérer la route et la méthode HTTP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// ROUTES FRONT (utilisateurs visiteurs)

// Récupérer toutes les JPO
if ($uri === '/jpo' && $method === 'GET') {
    $controller = new JpoController($db);
    $controller->getAll();
}

// Inscription à une JPO
elseif ($uri === '/inscription' && $method === 'POST') {
    $controller = new InscriptionController($db);
    $controller->create();
}

// Désinscription d’un visiteur
elseif ($uri === '/desinscription' && $method === 'DELETE') {
    $controller = new InscriptionController($db);
    $controller->delete(); // À implémenter
}

// Ajouter un commentaire
elseif ($uri === '/commentaire' && $method === 'POST') {
    $controller = new CommentController($db);
    $controller->create();
}

// Récupérer les commentaires pour une JPO (par ex. /commentaire?id_jpo=3)
elseif ($uri === '/commentaire' && $method === 'GET') {
    $controller = new CommentController($db);
    $controller->getAllByJpo(); // À implémenter
}


// ROUTES ADMIN / DASHBOARD

// CRUD JPO - Ajouter une JPO
elseif ($uri === '/admin/jpo' && $method === 'POST') {
    $controller = new AdminController($db);
    $controller->createJpo();
}

// Modifier une JPO
elseif ($uri === '/admin/jpo' && $method === 'PUT') {
    $controller = new AdminController($db);
    $controller->updateJpo();
}

// Supprimer une JPO
elseif ($uri === '/admin/jpo' && $method === 'DELETE') {
    $controller = new AdminController($db);
    $controller->deleteJpo();
}

// Récupérer les statistiques
elseif ($uri === '/admin/stats' && $method === 'GET') {
    $controller = new AdminController($db);
    $controller->getStats();
}

// Modérer un commentaire
elseif ($uri === '/admin/moderation' && $method === 'PUT') {
    $controller = new CommentController($db);
    $controller->moderate();
}

// Modifier contenu du site (ex: infos pratiques)
elseif ($uri === '/admin/contenu' && $method === 'PUT') {
    $controller = new AdminController($db);
    $controller->updateContent();
}


// ROUTE PAR DÉFAUT : 404
else {
    http_response_code(404);
    echo json_encode(["message" => "Route non trouvée"]);
}
