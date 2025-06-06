<?php
namespace Repository;

use App\Models\JPO;
use PDO;

class JpoRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(array $filters = []): array {
        $query = "SELECT * FROM jpo WHERE 1=1";
        $params = [];

        if (!empty($filters['place'])) {
            $query .= " AND place = :place";
            $params[':place'] = $filters['place'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        $query .= " ORDER BY date_jpo ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, JPO::class);
    }

    public function findById(int $id): ?JPO {
        $stmt = $this->db->prepare("SELECT * FROM jpo WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, JPO::class);
        return $stmt->fetch() ?: null;
    }

    public function create(JPO $jpo): ?int {
        $stmt = $this->db->prepare("
            INSERT INTO jpo (description, date_jpo, place, capacity, registered, status)
            VALUES (:description, :date_jpo, :place, :capacity, :registered, :status)
        ");
        $success = $stmt->execute([
            'description' => $jpo->description,
            'date_jpo' => $jpo->date_jpo,
            'place' => $jpo->place,
            'capacity' => $jpo->capacity,
            'registered' => $jpo->registered ?? 0,
            'status' => $jpo->status
        ]);

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    public function update(int $id, JPO $jpo): bool {
        $stmt = $this->db->prepare("
            UPDATE jpo SET
                description = :description,
                date_jpo = :date_jpo,
                place = :place,
                capacity = :capacity,
                status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'description' => $jpo->description,
            'date_jpo' => $jpo->date_jpo,
            'place' => $jpo->place,
            'capacity' => $jpo->capacity,
            'status' => $jpo->status,
            'id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM jpo WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function incrementRegistered(int $jpoId): bool {
        $stmt = $this->db->prepare("UPDATE jpo SET registered = registered + 1 WHERE id = :id");
        return $stmt->execute(['id' => $jpoId]);
    }

    public function decrementRegistered(int $jpoId): bool {
        $stmt = $this->db->prepare("UPDATE jpo SET registered = registered - 1 WHERE id = :id AND registered > 0");
        return $stmt->execute(['id' => $jpoId]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM jpo")->fetchColumn();
    }

    public function countByStatus(string $status): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM jpo WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    public function getStatsByPlace(): array {
        $stmt = $this->db->query("SELECT place, COUNT(*) as total_jpo FROM jpo GROUP BY place ORDER BY total_jpo DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsByMonth(): array {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(date_jpo, '%Y-%m') AS month, COUNT(*) as total_jpo
            FROM jpo
            GROUP BY month
            ORDER BY month DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlaces(): array {
        return ['Marseille', 'Paris', 'Cannes', 'Martigues', 'Toulon', 'Brignoles'];
    }
}
