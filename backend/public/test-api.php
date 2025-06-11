<?php
// backend/public/test.php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "message" => "Serveur PHP fonctionne !",
    "time" => date("Y-m-d H:i:s")
]);
?>