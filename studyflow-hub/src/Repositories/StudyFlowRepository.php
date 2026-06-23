<?php

declare(strict_types=1);

namespace StudyFlow\Repositories;

use PDO;
use StudyFlow\Core\Database;

class StudyFlowRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM studyflows WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $flow = $stmt->fetch();
        return $flow ? $flow : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT sf.*, u.username, (SELECT COUNT(*) FROM pins WHERE studyflow_id = sf.id) as pin_count 
             FROM studyflows sf 
             LEFT JOIN users u ON sf.user_id = u.id 
             WHERE sf.slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $flow = $stmt->fetch();
        return $flow ? $flow : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO studyflows (user_id, slug, title, description, is_pinned, is_public) 
             VALUES (:user_id, :slug, :title, :description, :is_pinned, :is_public)'
        );
        $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'slug' => $data['slug'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_pinned' => !empty($data['is_pinned']) ? 1 : 0,
            'is_public' => !empty($data['is_public']) ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE studyflows SET slug = :slug, title = :title, description = :description, 
             is_pinned = :is_pinned, is_public = :is_public, updated_at = CURRENT_TIMESTAMP 
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'slug' => $data['slug'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_pinned' => !empty($data['is_pinned']) ? 1 : 0,
            'is_public' => !empty($data['is_public']) ? 1 : 0,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM studyflows WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function getPaginated(string $search = '', string $sortBy = 'created_at', string $sortDir = 'desc', int $page = 1, int $perPage = 6, ?int $userId = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Allowed sort columns validation
        $allowedSort = ['created_at', 'title', 'slug'];
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }
        
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = 'WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $whereClause .= ' AND (title ILIKE :search OR description ILIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($userId !== null) {
            $whereClause .= ' AND user_id = :user_id';
            $params['user_id'] = $userId;
        }

        // Count total
        $countQuery = "SELECT COUNT(*) FROM studyflows $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $totalItems = (int) $countStmt->fetchColumn();

        // Get items
        $query = "SELECT * FROM studyflows $whereClause ORDER BY is_pinned DESC, $sortBy $sortDir LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $totalItems,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($totalItems / $perPage),
        ];
    }

    public function getTrending(int $limit = 4): array
    {
        // Simple trending query (can be based on created time or pinned state)
        $stmt = $this->db->prepare('SELECT * FROM studyflows ORDER BY is_pinned DESC, created_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
