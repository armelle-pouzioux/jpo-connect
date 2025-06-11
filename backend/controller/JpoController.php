<?php
namespace Controller;

use Models\JPO;
use Models\Comment;
use Models\Registration;
use Controller\AuthController;

class JpoController {
    private $jpoModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->jpoModel = new JPO($db);
    }

    /**
     * GET /jpo → Liste des JPO visibles
     */
    public function index() {
        $place = $_GET['place'] ?? null;
        $status = $_GET['status'] ?? 'upcoming';

        $filters = [];
        if (!empty($place)) $filters['place'] = $place;
        if (!empty($status)) $filters['status'] = $status;

        $jpos = $this->jpoModel->findAll($filters);
        $places = $this->jpoModel->getPlaces();

        echo json_encode([
            "success" => true,
            "jpos" => $jpos,
            "places" => $places
        ]);
    }

    /**
     * GET /jpo/{id} → Afficher une JPO + commentaires
     */
    public function show($id) {
        $jpo = $this->jpoModel->findById($id);

        if (!$jpo) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "JPO introuvable"]);
            return;
        }

        $commentModel = new Comment($this->db);
        $comments = $commentModel->findByJpo($id, 'approved');

        echo json_encode([
            "success" => true,
            "jpo" => $jpo,
            "comments" => $comments
        ]);
    }

    /**
     * GET /admin/jpo → Liste complète admin
     */
    public function adminIndex() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        $jpos = $this->jpoModel->findAll();
        echo json_encode([
            "success" => true,
            "jpos" => $jpos
        ]);
    }

    /**
     * POST /admin/jpo → Créer une JPO
     */
    public function store() {
        AuthController::requireRole(['manager', 'director']);

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || empty($data['date']) || empty($data['time']) || empty($data['place']) || empty($data['capacity'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Champs obligatoires manquants"]);
            return;
        }

        $description = $data['description'] ?? '';
        $datetime = date('Y-m-d H:i:s', strtotime($data['date'] . ' ' . $data['time']));

        $jpoId = $this->jpoModel->create([
            'description' => $description,
            'date_jpo' => $datetime,
            'place' => $data['place'],
            'capacity' => (int)$data['capacity'],
            'registered' => 0,
            'status' => 'upcoming'
        ]);

        echo json_encode([
            "success" => (bool)$jpoId,
            "jpo_id" => $jpoId
        ]);
    }

    /**
     * PUT /admin/jpo → Modifier une JPO
     */
    public function update($id) {
        // AuthController::requireRole(['manager', 'director']);

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Pas de données reçues"]);
            return;
        }

        $datetime = date('Y-m-d H:i:s', strtotime($data['date'] . ' ' . $data['time']));
        $jpoData = [
            'description' => $data['description'] ?? '',
            'date_jpo' => $datetime,
            'place' => $data['place'],
            'capacity' => (int)$data['capacity'],
            'status' => $data['status']
        ];

        $success = $this->jpoModel->update($id, $jpoData);
        echo json_encode(["success" => $success]);
    }

    /**
     * DELETE /admin/jpo → Supprimer une JPO
     */
    public function delete($id) {
        AuthController::requireRole(['director']);

        $success = $this->jpoModel->delete($id);
        echo json_encode(["success" => $success]);
    }

    /**
     * PUT /admin/jpo/finish/{id}
     */
    public function markAsFinished($id) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        $success = $this->jpoModel->update($id, ['status' => 'finished']);
        echo json_encode(["success" => $success]);
    }

    /**
     * PUT /admin/jpo/cancel/{id}
     */
    public function markAsCanceled($id) {
        AuthController::requireRole(['manager', 'director']);
        $success = $this->jpoModel->update($id, ['status' => 'canceled']);

        // Tu peux ajouter une logique d'envoi de mail ici plus tard si tu veux
        echo json_encode(["success" => $success, "message" => "Statut mis à jour"]);
    }
}
