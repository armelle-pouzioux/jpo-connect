<?php
namespace Controller;

use Models\JPO;
use Models\Comment;
use Models\Registration;
use Repository\JpoRepository;
use Controller\AuthController;
use Utils\Mailer;

class JPOController {
    private JpoRepository $jpoRepository;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->jpoRepository = new JpoRepository($db);
    }

    public function index() {
        $place = filter_input(INPUT_GET, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        $filters = [];
        if (!empty($place)) $filters['place'] = $place;
        $filters['status'] = $status ?: 'upcoming';

        $jpos = $this->jpoRepository->findAll($filters);
        $places = $this->jpoRepository->getPlaces();

        require_once __DIR__ . '/../view/jpo/index.php';
    }

    public function show($id) {
        $jpo = $this->jpoRepository->findById($id);

        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /jpo');
            exit;
        }

        $commentModel = new Comment($this->db); // Pass the db connection
        $comments = $commentModel->findByJpo($id, 'approved');

        require_once __DIR__ . '/../view/jpo/show.php';
    }

    public function adminIndex() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        $jpos = $this->jpoRepository->findAll();
        require_once __DIR__ . '/../view/admin/jpo/index.php';
    }

    public function create() {
        AuthController::requireRole(['manager', 'director']);
        require_once __DIR__ . '/../view/admin/jpo/create.php';
    }

    public function store() {
        AuthController::requireRole(['manager', 'director']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
            $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
            $place = filter_input(INPUT_POST, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
            $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);

            $validPlaces = $this->jpoRepository->getPlaces();
            if (!$date || !$time || !$place || !$capacity || $capacity <= 0 || !in_array($place, $validPlaces)) {
                $_SESSION['error'] = "Champs invalides ou manquants";
                header('Location: /admin/jpo/create');
                exit;
            }

            $dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));

            $jpo = new JPO();
            $jpo->description = $description;
            $jpo->date_jpo = $dateTime;
            $jpo->place = $place;
            $jpo->capacity = $capacity;
            $jpo->registered = 0;
            $jpo->status = 'upcoming';

            $jpoId = $this->jpoRepository->create($jpo);

            $_SESSION[$jpoId ? 'success' : 'error'] = $jpoId
                ? "La JPO a été créée avec succès"
                : "Une erreur est survenue lors de la création";
            header('Location: /admin/jpo');
            exit;
        }
    }

    public function edit($id) {
        AuthController::requireRole(['manager', 'director']);
        $jpo = $this->jpoRepository->findById($id);

        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }

        require_once __DIR__ . '/../view/admin/jpo/edit.php';
    }

    public function update($id) {
        AuthController::requireRole(['manager', 'director']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
            $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
            $place = filter_input(INPUT_POST, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
            $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

            $jpo = $this->jpoRepository->findById($id);
            if (!$jpo) {
                $_SESSION['error'] = "JPO non trouvée";
                header('Location: /admin/jpo');
                exit;
            }

            $validPlaces = $this->jpoRepository->getPlaces();
            $validStatuses = ['upcoming', 'finished', 'canceled'];
            if (!$date || !$time || !$place || !$capacity || !$status ||
                !in_array($place, $validPlaces) ||
                !in_array($status, $validStatuses) ||
                $capacity < $jpo->registered) {
                $_SESSION['error'] = "Champs invalides ou incohérents";
                header("Location: /admin/jpo/edit/$id");
                exit;
            }

            $dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));

            $jpoData = new JPO();
            $jpoData->description = $description;
            $jpoData->date_jpo = $dateTime;
            $jpoData->place = $place;
            $jpoData->capacity = $capacity;
            $jpoData->status = $status;

            $success = $this->jpoRepository->update($id, $jpoData);

            $_SESSION[$success ? 'success' : 'error'] = $success
                ? "La JPO a été mise à jour"
                : "Erreur lors de la mise à jour";
            header('Location: /admin/jpo');
            exit;
        }
    }

    public function delete($id) {
        AuthController::requireRole(['director']);

        $jpo = $this->jpoRepository->findById($id);

        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
        } else {
            $success = $this->jpoRepository->delete($id);
            $_SESSION[$success ? 'success' : 'error'] = $success
                ? "JPO supprimée"
                : "Erreur lors de la suppression";
        }

        header('Location: /admin/jpo');
        exit;
    }

    public function markAsFinished($id) {
        AuthController::requireRole(['employee', 'manager', 'director']);

        $jpo = $this->jpoRepository->findById($id);
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
        } else {
            $update = new JPO();
            $update->description = $jpo->description;
            $update->date_jpo = $jpo->date_jpo;
            $update->place = $jpo->place;
            $update->capacity = $jpo->capacity;
            $update->status = 'finished';

            $success = $this->jpoRepository->update($id, $update);
            $_SESSION[$success ? 'success' : 'error'] = $success
                ? "La JPO a été marquée comme terminée"
                : "Erreur lors de la mise à jour";
        }

        header('Location: /admin/jpo');
        exit;
    }

    public function markAsCanceled($id) {
        AuthController::requireRole(['manager', 'director']);

        $jpo = $this->jpoRepository->findById($id);
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }

        $update = new JPO();
        $update->description = $jpo->description;
        $update->date_jpo = $jpo->date_jpo;
        $update->place = $jpo->place;
        $update->capacity = $jpo->capacity;
        $update->status = 'canceled';

        $success = $this->jpoRepository->update($id, $update);

        if ($success) {
            $_SESSION['success'] = "JPO annulée";

            $registrationModel = new Registration();
            $registrations = $registrationModel->findByJpo($id);

            $mailer = new Mailer();
            foreach ($registrations as $registration) {
                $mailer->sendJpoCancelationEmail($registration['email'], $jpo);
            }
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation";
        }

        header('Location: /admin/jpo');
        exit;
    }
}
