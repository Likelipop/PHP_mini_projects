<?php

declare(strict_types=1);

namespace StudyFlow\Repositories;

use PDO;
use StudyFlow\Core\Database;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
        );
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getUserStats(int $userId): array
    {
        // 1. Total resources
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM assets a 
             JOIN studyflows sf ON a.studyflow_id = sf.id 
             WHERE sf.user_id = :user_id AND a.type = \'resource\''
        );
        $stmt->execute(['user_id' => $userId]);
        $resourcesCount = (int)$stmt->fetchColumn();

        // 2. Total notes
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM assets a 
             JOIN studyflows sf ON a.studyflow_id = sf.id 
             WHERE sf.user_id = :user_id AND a.type = \'note\''
        );
        $stmt->execute(['user_id' => $userId]);
        $notesCount = (int)$stmt->fetchColumn();

        // 3. Total distinct tags
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT t.id) FROM tags t 
             JOIN asset_tags at ON t.id = at.tag_id 
             JOIN assets a ON at.asset_id = a.id 
             JOIN studyflows sf ON a.studyflow_id = sf.id 
             WHERE sf.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        $tagsCount = (int)$stmt->fetchColumn();

        // 4. Total pinned studyflows
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM studyflows 
             WHERE user_id = :user_id AND is_pinned = TRUE'
        );
        $stmt->execute(['user_id' => $userId]);
        $pinsCount = (int)$stmt->fetchColumn();

        // 5. Top 5 tags usage for Pie Chart
        $stmt = $this->db->prepare(
            'SELECT t.name, COUNT(*) as count FROM tags t 
             JOIN asset_tags at ON t.id = at.tag_id 
             JOIN assets a ON at.asset_id = a.id 
             JOIN studyflows sf ON a.studyflow_id = sf.id 
             WHERE sf.user_id = :user_id 
             GROUP BY t.id, t.name 
             ORDER BY count DESC LIMIT 5'
        );
        $stmt->execute(['user_id' => $userId]);
        $topTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Weekly upload activity (last 7 days counts)
        // EXTRACT(ISODOW FROM a.created_at) returns 1 (Monday) to 7 (Sunday)
        $stmt = $this->db->prepare(
            'SELECT EXTRACT(ISODOW FROM a.created_at) as dow, COUNT(*) as count 
             FROM assets a 
             JOIN studyflows sf ON a.studyflow_id = sf.id 
             WHERE sf.user_id = :user_id AND a.created_at >= CURRENT_DATE - INTERVAL \'6 days\'
             GROUP BY dow'
        );
        $stmt->execute(['user_id' => $userId]);
        $weeklyActivityRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $weeklyActivity = [
            'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0
        ];
        $dowMap = [
            1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'
        ];
        foreach ($weeklyActivityRaw as $row) {
            $dowNum = (int)$row['dow'];
            if (isset($dowMap[$dowNum])) {
                $weeklyActivity[$dowMap[$dowNum]] = (int)$row['count'];
            }
        }

        return [
            'resources_count' => $resourcesCount,
            'notes_count' => $notesCount,
            'tags_count' => $tagsCount,
            'pins_count' => $pinsCount,
            'top_tags' => $topTags,
            'weekly_activity' => $weeklyActivity
        ];
    }
}
