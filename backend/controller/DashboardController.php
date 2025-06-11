<?php
namespace Controller;

use Models\Jpo;
use Models\User; 
use Models\Registration;
use Models\Comment;
use Controller\AuthController;

class DashboardController {
    private $jpoModel;
    private $userModel;
    private $registrationModel;
    private $commentModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->jpoModel = new Jpo($db);
        $this->userModel = new User($db);
        $this->registrationModel = new Registration($db);
        $this->commentModel = new Comment($db);
    }

    /**
     * Affiche le tableau de bord administrateur
     */
    public function index() {
        AuthController::requireRole(['employee', 'manager', 'director']);
        
        // Statistiques générales
        $stats = [
            'total_jpo' => $this->jpoModel->count(),
            'upcoming_jpo' => $this->jpoModel->countByStatus('upcoming'),
            'finished_jpo' => $this->jpoModel->countByStatus('finished'),
            'canceled_jpo' => $this->jpoModel->countByStatus('canceled'),
            'total_users' => $this->userModel->count(),
            'total_registrations' => $this->registrationModel->count(),
            'pending_comments' => $this->commentModel->countByStatus('awaiting')
        ];
        
        // JPO à venir
        $upcomingJpos = $this->jpoModel->findByStatus('upcoming', 5);
        
        // Dernières inscriptions
        $latestRegistrations = $this->registrationModel->findLatest(5);
        
        // Commentaires en attente
        $pendingComments = $this->commentModel->findByStatus('awaiting', 5);
        
        require_once __DIR__ . '/../view/admin/dashboard/index.php';
    }

    /**
     * Affiche les statistiques détaillées
     */
    public function statistics() {
        AuthController::requireRole(['manager', 'director']);
        
        // Statistiques par ville
        $statsByPlace = $this->jpoModel->getStatsByPlace();
        
        // Statistiques par mois
        $statsByMonth = $this->jpoModel->getStatsByMonth();
        
        // Taux de présence
        $attendanceRate = $this->registrationModel->getAttendanceRate();
        
        // Utilisateurs par rôle
        $usersByRole = $this->userModel->countByRole();
        
        require_once __DIR__ . '/../view/admin/dashboard/statistics.php';
    }

    /**
     * Exporte les données au format CSV
     */
    public function exportData($type) {
        AuthController::requireRole(['director']);
        
        switch ($type) {
            case 'users':
                $data = $this->userModel->findAll();
                $filename = 'users_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Rôle', 'Date de création'];
                break;
                
            case 'jpo':
                $data = $this->jpoModel->findAll();
                $filename = 'jpo_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Description', 'Date', 'Lieu', 'Capacité', 'Inscrits', 'Statut'];
                break;
                
            case 'registrations':
                $data = $this->registrationModel->findAllWithDetails();
                $filename = 'registrations_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Utilisateur', 'Email', 'JPO', 'Date', 'Lieu', 'Date d\'inscription', 'Présence'];
                break;
                
            default:
                $_SESSION['error'] = "Type d'export non valide";
                header('Location: /admin/dashboard/statistics');
                exit;
        }
        
        // Générer le CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Ajouter les en-têtes
        fputcsv($output, $headers);
        
        // Ajouter les données
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}