<?php
require_once __DIR__ . '/../Models/JPO.php';
require_once __DIR__ . '/AuthController.php';

class JPOController {
    private $jpoModel;

    public function __construct() {
        $this->jpoModel = new JPO();
    }

    /**
     * Affiche la liste des JPO pour les visiteurs
     */
    public function index() {
        $place = filter_input(INPUT_GET, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Filtrer les JPO selon les paramètres
        $filters = [];
        if (!empty($place)) {
            $filters['place'] = $place;
        }
        if (!empty($status)) {
            $filters['status'] = $status;
        } else {
            // Par défaut, afficher uniquement les JPO à venir
            $filters['status'] = 'upcoming';
        }
        
        $jpos = $this->jpoModel->findAll($filters);
        
        // Récupérer la liste des villes pour le filtre
        $places = $this->jpoModel->getPlaces();
        
        require_once __DIR__ . '/../view/jpo/index.php';
    }

    /**
     * Affiche les détails d'une JPO
     */
    public function show($id) {
        $jpo = $this->jpoModel->findById($id);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /jpo');
            exit;
        }
        
        // Charger les commentaires approuvés pour cette JPO
        require_once __DIR__ . '/../model/Comment.php';
        $commentModel = new Comment();
        $comments = $commentModel->findByJpo($id, 'approved');
        
        require_once __DIR__ . '/../view/jpo/show.php';
    }

    /**
     * Affiche la liste des JPO (admin)
     */
    public function adminIndex() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        $jpos = $this->jpoModel->findAll();
        require_once __DIR__ . '/../view/admin/jpo/index.php';
    }

    /**
     * Affiche le formulaire de création d'une JPO (admin)
     */
    public function create() {
        AuthController::requireRole(['manager', 'director']);
        
        require_once __DIR__ . '/../view/admin/jpo/create.php';
    }

    /**
     * Traite la création d'une JPO (admin)
     */
    public function store() {
        AuthController::requireRole(['manager', 'director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
            $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
            $place = filter_input(INPUT_POST, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
            $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
            
            // Validation des données
            if (empty($date) || empty($time) || empty($place) || empty($capacity)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /admin/jpo/create');
                exit;
            }
            
            // Vérifier que la place est valide
            $validPlaces = ['Marseille', 'Paris', 'Cannes', 'Martigues', 'Toulon', 'Brignoles'];
            if (!in_array($place, $validPlaces)) {
                $_SESSION['error'] = "Le lieu sélectionné n'est pas valide";
                header('Location: /admin/jpo/create');
                exit;
            }
            
            // Vérifier que la capacité est un nombre positif
            if ($capacity <= 0) {
                $_SESSION['error'] = "La capacité doit être un nombre positif";
                header('Location: /admin/jpo/create');
                exit;
            }
            
            // Formater la date et l'heure
            $dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));
            
            // Création de la JPO
            $jpoId = $this->jpoModel->create([
                'description' => $description,
                'date_jpo' => $dateTime,
                'place' => $place,
                'capacity' => $capacity,
                'registered' => 0,
                'status' => 'upcoming'
            ]);
            
            if ($jpoId) {
                $_SESSION['success'] = "La JPO a été créée avec succès";
                header('Location: /admin/jpo');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la création de la JPO";
                header('Location: /admin/jpo/create');
                exit;
            }
        }
    }

    /**
     * Affiche le formulaire d'édition d'une JPO (admin)
     */
    public function edit($id) {
        AuthController::requireRole(['manager', 'director']);
        
        $jpo = $this->jpoModel->findById($id);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        require_once __DIR__ . '/../view/admin/jpo/edit.php';
    }

    /**
     * Traite la mise à jour d'une JPO (admin)
     */
    public function update($id) {
        AuthController::requireRole(['manager', 'director']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
            $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
            $place = filter_input(INPUT_POST, 'place', FILTER_SANITIZE_SPECIAL_CHARS);
            $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Validation des données
            if (empty($date) || empty($time) || empty($place) || empty($capacity) || empty($status)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
            
            // Vérifier que la JPO existe
            $jpo = $this->jpoModel->findById($id);
            if (!$jpo) {
                $_SESSION['error'] = "JPO non trouvée";
                header('Location: /admin/jpo');
                exit;
            }
            
            // Vérifier que la place est valide
            $validPlaces = ['Marseille', 'Paris', 'Cannes', 'Martigues', 'Toulon', 'Brignoles'];
            if (!in_array($place, $validPlaces)) {
                $_SESSION['error'] = "Le lieu sélectionné n'est pas valide";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
            
            // Vérifier que le statut est valide
            $validStatuses = ['upcoming', 'finished', 'canceled'];
            if (!in_array($status, $validStatuses)) {
                $_SESSION['error'] = "Le statut sélectionné n'est pas valide";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
            
            // Vérifier que la capacité est un nombre positif
            if ($capacity <= 0) {
                $_SESSION['error'] = "La capacité doit être un nombre positif";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
            
            // Vérifier que la capacité est supérieure ou égale au nombre d'inscrits
            if ($capacity < $jpo['registered']) {
                $_SESSION['error'] = "La capacité ne peut pas être inférieure au nombre d'inscrits";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
            
            // Formater la date et l'heure
            $dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));
            
            // Mise à jour de la JPO
            $jpoData = [
                'description' => $description,
                'date_jpo' => $dateTime,
                'place' => $place,
                'capacity' => $capacity,
                'status' => $status
            ];
            
            $success = $this->jpoModel->update($id, $jpoData);
            
            if ($success) {
                $_SESSION['success'] = "La JPO a été mise à jour avec succès";
                header('Location: /admin/jpo');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de la JPO";
                header('Location: /admin/jpo/edit/' . $id);
                exit;
            }
        }
    }

    /**
     * Supprime une JPO (admin)
     */
    public function delete($id) {
        AuthController::requireRole(['director']);
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($id);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Supprimer la JPO
        $success = $this->jpoModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "La JPO a été supprimée avec succès";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression de la JPO";
        }
        
        header('Location: /admin/jpo');
        exit;
    }

    /**
     * Marque une JPO comme terminée (admin)
     */
    public function markAsFinished($id) {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($id);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Mettre à jour le statut
        $success = $this->jpoModel->update($id, ['status' => 'finished']);
        
        if ($success) {
            $_SESSION['success'] = "La JPO a été marquée comme terminée";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du statut";
        }
        
        header('Location: /admin/jpo');
        exit;
    }

    /**
     * Marque une JPO comme annulée (admin)
     */
    public function markAsCanceled($id) {
        AuthController::requireRole(['manager', 'director']);
        
        // Vérifier si la JPO existe
        $jpo = $this->jpoModel->findById($id);
        
        if (!$jpo) {
            $_SESSION['error'] = "JPO non trouvée";
            header('Location: /admin/jpo');
            exit;
        }
        
        // Mettre à jour le statut
        $success = $this->jpoModel->update($id, ['status' => 'canceled']);
        
        if ($success) {
            $_SESSION['success'] = "La JPO a été marquée comme annulée";
            
            // Envoyer un email à tous les inscrits pour les informer de l'annulation
            require_once __DIR__ . '/../model/Registration.php';
            $registrationModel = new Registration();
            $registrations = $registrationModel->findByJpo($id);
            
            if (!empty($registrations)) {
                require_once __DIR__ . '/../utils/Mailer.php';
                $mailer = new Mailer();
                
                foreach ($registrations as $registration) {
                    $mailer->sendJpoCancelationEmail($registration['email'], $jpo);
                }
            }
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du statut";
        }
        
        header('Location: /admin/jpo');
        exit;
    }
}