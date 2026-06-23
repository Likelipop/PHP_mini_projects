<?php

declare(strict_types=1);

namespace StudyFlow\Repositories;

use PDO;
use StudyFlow\Core\Database;

class AssetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, rm.filename, rm.folder_name, nm.markdown 
             FROM assets a
             LEFT JOIN resource_metadata rm ON a.id = rm.asset_id
             LEFT JOIN note_metadata nm ON a.id = nm.asset_id
             WHERE a.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $asset = $stmt->fetch();
        return $asset ? $asset : null;
    }

    public function checkDuplicateFilename(int $studyflowId, string $folderName, string $filename): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM assets a
             JOIN resource_metadata rm ON a.id = rm.asset_id
             WHERE a.studyflow_id = :studyflow_id 
               AND rm.folder_name = :folder_name 
               AND rm.filename = :filename'
        );
        $stmt->execute([
            'studyflow_id' => $studyflowId,
            'folder_name' => $folderName,
            'filename' => $filename
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createResource(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO assets (studyflow_id, type, title, content, storage_key, mime_type, tags) 
                 VALUES (:studyflow_id, \'resource\', :title, :content, :storage_key, :mime_type, :tags)'
            );
            $stmt->execute([
                'studyflow_id' => $data['studyflow_id'],
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'storage_key' => $data['storage_key'],
                'mime_type' => $data['mime_type'],
                'tags' => json_encode($data['tags'] ?? ['untagged']),
            ]);
            $assetId = (int)$this->db->lastInsertId();

            $stmtMeta = $this->db->prepare(
                'INSERT INTO resource_metadata (asset_id, filename, folder_name) 
                 VALUES (:asset_id, :filename, :folder_name)'
            );
            $stmtMeta->execute([
                'asset_id' => $assetId,
                'filename' => $data['filename'],
                'folder_name' => $data['folder_name'] ?: 'Root',
            ]);

            // Assign default 'untagged' tag if no tags are provided
            $tags = $data['tags'] ?? ['untagged'];
            $this->assignTags($assetId, $tags);

            $this->db->commit();
            return $assetId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createNote(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO assets (studyflow_id, type, title, content, tags) 
                 VALUES (:studyflow_id, \'note\', :title, :content, :tags)'
            );
            $stmt->execute([
                'studyflow_id' => $data['studyflow_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'tags' => json_encode($data['tags'] ?? ['untagged']),
            ]);
            $assetId = (int)$this->db->lastInsertId();

            $stmtMeta = $this->db->prepare(
                'INSERT INTO note_metadata (asset_id, markdown) 
                 VALUES (:asset_id, :markdown)'
            );
            $stmtMeta->execute([
                'asset_id' => $assetId,
                'markdown' => $data['content'],
            ]);

            // Assign default 'untagged' tag if no tags are provided
            $tags = $data['tags'] ?? ['untagged'];
            $this->assignTags($assetId, $tags);

            $this->db->commit();
            return $assetId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateNote(int $id, string $title, string $markdown): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE assets SET title = :title, content = :markdown, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $stmt->execute(['id' => $id, 'title' => $title, 'markdown' => $markdown]);

            $stmtMeta = $this->db->prepare(
                'UPDATE note_metadata SET markdown = :markdown WHERE asset_id = :asset_id'
            );
            $stmtMeta->execute(['asset_id' => $id, 'markdown' => $markdown]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM assets WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function assignTags(int $assetId, array $tagNames): void
    {
        // Update tags column in JSON format
        $stmtJson = $this->db->prepare('UPDATE assets SET tags = :tags WHERE id = :id');
        $stmtJson->execute(['tags' => json_encode($tagNames), 'id' => $assetId]);

        // First delete existing tags
        $stmtDel = $this->db->prepare('DELETE FROM asset_tags WHERE asset_id = :asset_id');
        $stmtDel->execute(['asset_id' => $assetId]);

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === '') {
                continue;
            }

            // Find or create tag
            $stmtTag = $this->db->prepare('SELECT id FROM tags WHERE prefix = :prefix');
            $prefix = strtolower($tagName);
            $stmtTag->execute(['prefix' => $prefix]);
            $tagId = $stmtTag->fetchColumn();

            if (!$tagId) {
                // Determine display name
                $parts = explode('/', $tagName);
                $name = end($parts);
                $stmtIns = $this->db->prepare('INSERT INTO tags (name, prefix) VALUES (:name, :prefix) RETURNING id');
                $stmtIns->execute(['name' => $name, 'prefix' => $prefix]);
                $tagId = $stmtIns->fetchColumn();
            }

            $stmtMap = $this->db->prepare('INSERT INTO asset_tags (asset_id, tag_id) VALUES (:asset_id, :tag_id) ON CONFLICT DO NOTHING');
            $stmtMap->execute(['asset_id' => $assetId, 'tag_id' => $tagId]);
        }
    }

    public function getAssetsByStudyFlow(int $studyflowId, array $filters = []): array
    {
        $whereClause = 'WHERE a.studyflow_id = :studyflow_id';
        $params = ['studyflow_id' => $studyflowId];

        if (isset($filters['type']) && $filters['type'] !== '') {
            $whereClause .= ' AND a.type = :type';
            $params['type'] = $filters['type'];
        }

        if (isset($filters['folder']) && $filters['folder'] !== '') {
            $whereClause .= ' AND rm.folder_name = :folder';
            $params['folder'] = $filters['folder'];
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $whereClause .= ' AND (a.title ILIKE :search OR a.content ILIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['tag']) && $filters['tag'] !== '') {
            $whereClause .= ' AND a.id IN (
                SELECT asset_id FROM asset_tags at 
                JOIN tags t ON at.tag_id = t.id 
                WHERE t.prefix = :tag_prefix OR t.prefix LIKE :tag_prefix_wildcard
            )';
            $params['tag_prefix'] = strtolower($filters['tag']);
            $params['tag_prefix_wildcard'] = strtolower($filters['tag']) . '/%';
        }

        $query = "SELECT a.*, rm.filename, rm.folder_name, nm.markdown 
                  FROM assets a
                  LEFT JOIN resource_metadata rm ON a.id = rm.asset_id
                  LEFT JOIN note_metadata nm ON a.id = nm.asset_id
                  $whereClause 
                  ORDER BY a.type DESC, a.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $assets = $stmt->fetchAll();

        // Hydrate tags for each asset
        foreach ($assets as &$asset) {
            $asset['tags'] = $this->getAssetTags((int)$asset['id']);
        }

        return $assets;
    }

    public function getAssetTags(int $assetId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM tags t
             JOIN asset_tags at ON t.id = at.tag_id
             WHERE at.asset_id = :asset_id'
        );
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    public function createFragment(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO asset_fragments (asset_id, tag_id, page, bbox, text, image_path) 
             VALUES (:asset_id, :tag_id, :page, :bbox, :text, :image_path)'
        );
        
        $tagId = null;
        if (isset($data['tag_name']) && $data['tag_name'] !== '') {
            $stmtTag = $this->db->prepare('SELECT id FROM tags WHERE prefix = :prefix');
            $prefix = strtolower($data['tag_name']);
            $stmtTag->execute(['prefix' => $prefix]);
            $tagId = $stmtTag->fetchColumn();

            if (!$tagId) {
                $parts = explode('/', $data['tag_name']);
                $name = end($parts);
                $stmtIns = $this->db->prepare('INSERT INTO tags (name, prefix) VALUES (:name, :prefix) RETURNING id');
                $stmtIns->execute(['name' => $name, 'prefix' => $prefix]);
                $tagId = $stmtIns->fetchColumn();
            }
        }

        $stmt->execute([
            'asset_id' => $data['asset_id'],
            'tag_id' => $tagId ? (int)$tagId : null,
            'page' => $data['page'] ?? null,
            'bbox' => $data['bbox'] ?? null,
            'text' => $data['text'] ?? null,
            'image_path' => $data['image_path'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getFragmentsByAsset(int $assetId): array
    {
        $stmt = $this->db->prepare(
            'SELECT af.*, t.prefix as tag_prefix, t.name as tag_name 
             FROM asset_fragments af
             LEFT JOIN tags t ON af.tag_id = t.id
             WHERE af.asset_id = :asset_id
             ORDER BY af.created_at ASC'
        );
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    public function searchEverywhere(string $query): array
    {
        $param = '%' . $query . '%';
        $results = [
            'notes' => [],
            'resources' => [],
            'tags' => [],
            'studyflows' => [],
        ];

        // 1. Search Notes
        $stmtNotes = $this->db->prepare(
            "SELECT a.id, a.title, a.content, sf.slug as flow_slug
             FROM assets a
             JOIN studyflows sf ON a.studyflow_id = sf.id
             WHERE a.type = 'note' AND (a.title ILIKE :q OR a.content ILIKE :q)
             LIMIT 10"
        );
        $stmtNotes->execute(['q' => $param]);
        $results['notes'] = $stmtNotes->fetchAll();

        // 2. Search Resources
        $stmtRes = $this->db->prepare(
            "SELECT a.id, a.title, rm.folder_name, sf.slug as flow_slug
             FROM assets a
             JOIN resource_metadata rm ON a.id = rm.asset_id
             JOIN studyflows sf ON a.studyflow_id = sf.id
             WHERE a.type = 'resource' AND (a.title ILIKE :q OR rm.folder_name ILIKE :q)
             LIMIT 10"
        );
        $stmtRes->execute(['q' => $param]);
        $results['resources'] = $stmtRes->fetchAll();

        // 3. Search Tags
        $stmtTags = $this->db->prepare(
            "SELECT t.id, t.name, t.prefix 
             FROM tags t 
             WHERE t.name ILIKE :q OR t.prefix ILIKE :q
             LIMIT 10"
        );
        $stmtTags->execute(['q' => $param]);
        $results['tags'] = $stmtTags->fetchAll();

        // 4. Search StudyFlows
        $stmtFlows = $this->db->prepare(
            "SELECT sf.id, sf.title, sf.slug, sf.description 
             FROM studyflows sf 
             WHERE sf.title ILIKE :q OR sf.description ILIKE :q
             LIMIT 10"
        );
        $stmtFlows->execute(['q' => $param]);
        $results['studyflows'] = $stmtFlows->fetchAll();

        return $results;
    }

    public function getRelatedAssets(int $flowId, string $tag): array
    {
        $tagLower = strtolower($tag);
        $tagWildcard = $tagLower . '/%';
        
        // 1. Fetch Assets (notes and resources)
        $stmtAssets = $this->db->prepare(
            'SELECT DISTINCT a.*, rm.folder_name, rm.filename 
             FROM assets a
             LEFT JOIN resource_metadata rm ON a.id = rm.asset_id
             JOIN asset_tags at ON a.id = at.asset_id
             JOIN tags t ON at.tag_id = t.id
             WHERE a.studyflow_id = :flow_id 
               AND (t.prefix = :tag OR t.prefix LIKE :tag_wildcard)
             ORDER BY a.type DESC, a.created_at DESC'
        );
        $stmtAssets->execute([
            'flow_id' => $flowId,
            'tag' => $tagLower,
            'tag_wildcard' => $tagWildcard
        ]);
        $assets = $stmtAssets->fetchAll();
        
        $notes = [];
        $resources = [];
        foreach ($assets as $asset) {
            if ($asset['type'] === 'note') {
                $notes[] = $asset;
            } else {
                $asset['presigned_url'] = \StudyFlow\Support\Storage::getDownloadUrl($asset['storage_key']);
                $resources[] = $asset;
            }
        }
        
        // 2. Fetch Fragments
        $stmtFrags = $this->db->prepare(
            'SELECT DISTINCT af.*, a.title as asset_title 
             FROM asset_fragments af
             JOIN assets a ON af.asset_id = a.id
             JOIN tags t ON af.tag_id = t.id
             WHERE a.studyflow_id = :flow_id 
               AND (t.prefix = :tag OR t.prefix LIKE :tag_wildcard)
             ORDER BY af.created_at DESC'
        );
        $stmtFrags->execute([
            'flow_id' => $flowId,
            'tag' => $tagLower,
            'tag_wildcard' => $tagWildcard
        ]);
        $fragments = $stmtFrags->fetchAll();
        
        return [
            'notes' => $notes,
            'resources' => $resources,
            'fragments' => $fragments
        ];
    }

    public function createFolder(array $data): int
    {
        // Check for duplicates (TC10 unique key constraint)
        $stmtCheck = $this->db->prepare('SELECT id FROM assets WHERE studyflow_id = :studyflow_id AND type = \'folder\' AND title = :title LIMIT 1');
        $stmtCheck->execute([
            'studyflow_id' => $data['studyflow_id'],
            'title' => $data['title']
        ]);
        if ($stmtCheck->fetch()) {
            throw new \Exception("Thư mục đã tồn tại.");
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO assets (studyflow_id, type, title) 
                 VALUES (:studyflow_id, \'folder\', :title)'
            );
            $stmt->execute([
                'studyflow_id' => $data['studyflow_id'],
                'title' => $data['title'],
            ]);
            $assetId = (int)$this->db->lastInsertId();

            $stmtMeta = $this->db->prepare(
                'INSERT INTO resource_metadata (asset_id, filename, folder_name, description) 
                 VALUES (:asset_id, \'\', :folder_name, :description)'
            );
            $stmtMeta->execute([
                'asset_id' => $assetId,
                'folder_name' => 'Root',
                'description' => $data['description'] ?? null,
            ]);

            $this->db->commit();
            return $assetId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateFolderName(int $id, string $newName): bool
    {
        // First get the old title of the folder asset
        $stmtOld = $this->db->prepare('SELECT title FROM assets WHERE id = :id AND type = \'folder\'');
        $stmtOld->execute(['id' => $id]);
        $oldTitle = $stmtOld->fetchColumn();

        if (!$oldTitle) return false;

        $this->db->beginTransaction();
        try {
            // Update the folder asset title
            $stmt = $this->db->prepare('UPDATE assets SET title = :title, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->execute(['id' => $id, 'title' => $newName]);

            // Update all resources that have this old folder_name
            $stmtMeta = $this->db->prepare('UPDATE resource_metadata SET folder_name = :new_name WHERE folder_name = :old_name');
            $stmtMeta->execute(['new_name' => $newName, 'old_name' => $oldTitle]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateAssetTitle(int $id, string $newTitle): bool
    {
        $stmt = $this->db->prepare('UPDATE assets SET title = :title, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute(['id' => $id, 'title' => $newTitle]);
    }

    public function moveAsset(int $id, string $folderName): bool
    {
        $stmt = $this->db->prepare('UPDATE resource_metadata SET folder_name = :folder_name WHERE asset_id = :id');
        return $stmt->execute(['id' => $id, 'folder_name' => $folderName]);
    }

    public function updateAssetOrder(array $orderIds): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE assets SET sort_order = :sort_order WHERE id = :id');
            foreach ($orderIds as $index => $id) {
                $stmt->execute(['sort_order' => $index, 'id' => $id]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
