<?php
// backend/controller/JpoController.php

use Models\JPO;

require_once __DIR__ . '/../model/Jpo.php';

class JpoController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $model = new JPO($this->db);
        $jpos = $model->getAll();

        if (count($jpos) > 0) {
            http_response_code(200);
            echo json_encode($jpos);
        } else {
            http_response_code(204); // No Content
            echo json_encode(["message" => "Aucune JPO trouvée."]);
        }
    }
}
