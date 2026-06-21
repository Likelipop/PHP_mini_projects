<?php

declare(strict_types=1);

namespace StudyFlow\Repositories;

use PDO;
use StudyFlow\Core\Database;

class TagRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllTagsForStudyFlow(int $studyflowId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT t.* FROM tags t
             JOIN asset_tags at ON t.id = at.tag_id
             JOIN assets a ON at.asset_id = a.id
             WHERE a.studyflow_id = :studyflow_id
             ORDER BY t.prefix ASC'
        );
        $stmt->execute(['studyflow_id' => $studyflowId]);
        return $stmt->fetchAll();
    }

    public function findOrCreate(string $name, string $prefix): int
    {
        $prefix = strtolower(trim($prefix));
        $name = trim($name);
        
        $stmt = $this->db->prepare('SELECT id FROM tags WHERE prefix = :prefix');
        $stmt->execute(['prefix' => $prefix]);
        $id = $stmt->fetchColumn();
        
        if ($id) {
            return (int)$id;
        }

        $stmtIns = $this->db->prepare('INSERT INTO tags (name, prefix) VALUES (:name, :prefix) RETURNING id');
        $stmtIns->execute(['name' => $name, 'prefix' => $prefix]);
        return (int)$stmtIns->fetchColumn();
    }

    public function searchTags(string $query): array
    {
        $stmt = $this->db->prepare('SELECT * FROM tags WHERE prefix ILIKE :query ORDER BY prefix LIMIT 10');
        $stmt->execute(['query' => '%' . strtolower($query) . '%']);
        return $stmt->fetchAll();
    }
}
