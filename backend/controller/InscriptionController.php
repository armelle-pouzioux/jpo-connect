<?php
// backend/controller/InscriptionController.php

require_once __DIR__ . '/../model/Inscription.php';

class InscriptionController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['jpo_id']) ||
            empty($data['nom']) ||
            empty($data['prenom']) ||
            empty($data['email'])
        ) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Champs requis manquants."]);
            return;
        }

        $jpo_id = intval($data['jpo_id']);
        $nom = trim($data['nom']);
        $prenom = trim($data['prenom']);
        $email = trim($data['email']);
        $telephone = isset($data['telephone']) ? trim($data['telephone']) : null;

        $model = new Inscription($this->db);
        $result = $model->inscrire($jpo_id, $nom, $prenom, $email, $telephone);

        if ($result === true) {
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Inscription réussie !"]);
        } else {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => $result]);
        }
    }
}
