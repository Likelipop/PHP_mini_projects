<?php

declare(strict_types=1);

namespace StudyFlow\Services;

use StudyFlow\Repositories\AssetRepository;
use StudyFlow\Support\Storage;

class AssetService
{
    private AssetRepository $assetRepository;

    public function __construct()
    {
        $this->assetRepository = new AssetRepository();
    }

    public function getAssetById(int $id): ?array
    {
        return $this->assetRepository->findById($id);
    }

    public function getAssets(int $studyflowId, array $filters = []): array
    {
        return $this->assetRepository->getAssetsByStudyFlow($studyflowId, $filters);
    }

    public function createResource(int $studyflowId, array $fileInfo, string $folderName, array $tags = []): array
    {
        $filename = basename($fileInfo['name']);
        
        // 1. Check for duplicates in the same subfolder
        $folderName = trim($folderName) !== '' ? trim($folderName) : 'Root';
        if ($this->assetRepository->checkDuplicateFilename($studyflowId, $folderName, $filename)) {
            return [
                'success' => false,
                'error' => "Tệp tin '$filename' đã tồn tại trong thư mục '$folderName'."
            ];
        }

        // 2. Upload to MinIO/S3
        $uniqueKey = 'flows/' . $studyflowId . '/' . strtolower($folderName) . '/' . time() . '_' . $filename;
        $uploadSuccess = Storage::upload($uniqueKey, $fileInfo['tmp_name']);

        if (!$uploadSuccess) {
            return [
                'success' => false,
                'error' => 'Không thể tải tệp tin lên hệ thống lưu trữ.'
            ];
        }

        // 3. Save to database
        try {
            $assetId = $this->assetRepository->createResource([
                'studyflow_id' => $studyflowId,
                'title' => $filename,
                'content' => '',
                'storage_key' => $uniqueKey,
                'mime_type' => $fileInfo['type'],
                'filename' => $filename,
                'folder_name' => $folderName,
                'tags' => $tags,
            ]);

            return ['success' => true, 'id' => $assetId];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ];
        }
    }

    public function extractTagsFromMarkdown(string $markdown): array
    {
        preg_match_all('/@([a-zA-Z0-9_\-\/]+)/', $markdown, $matches);
        $tags = [];
        if (!empty($matches[1])) {
            $db = \StudyFlow\Core\Database::getConnection();
            foreach ($matches[1] as $ref) {
                $refLower = strtolower(trim($ref));
                // Skip files and pages transclusions
                if (preg_match('/\.(pdf|png|jpg|jpeg|gif|zip)$/i', $refLower) || preg_match('/_page\d+$/i', $refLower)) {
                    continue;
                }
                
                $stmt = $db->prepare('SELECT prefix FROM tags WHERE LOWER(name) = :ref OR prefix = :ref');
                $stmt->execute(['ref' => $refLower]);
                $matchedPrefix = $stmt->fetchColumn();
                if ($matchedPrefix) {
                    $tags[] = $matchedPrefix;
                } else {
                    if (str_contains($refLower, '/')) {
                        $tags[] = $refLower;
                    }
                }
            }
        }
        return array_unique($tags);
    }

    public function createNote(int $studyflowId, string $title, string $markdown, array $tags = []): array
    {
        $title = trim($title);
        if ($title === '') {
            return ['success' => false, 'error' => 'Tiêu đề ghi chú không được để trống.'];
        }

        $extracted = $this->extractTagsFromMarkdown($markdown);
        $tags = array_unique(array_merge($tags, $extracted));
        if (empty($tags)) {
            $tags = ['untagged'];
        }
        if (count($tags) > 1 && in_array('untagged', $tags, true)) {
            $tags = array_diff($tags, ['untagged']);
        }

        try {
            $assetId = $this->assetRepository->createNote([
                'studyflow_id' => $studyflowId,
                'title' => $title,
                'content' => $markdown,
                'tags' => $tags,
            ]);

            return ['success' => true, 'id' => $assetId];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ];
        }
    }

    public function updateNote(int $id, string $title, string $markdown, array $tags = []): array
    {
        $title = trim($title);
        if ($title === '') {
            return ['success' => false, 'error' => 'Tiêu đề ghi chú không được để trống.'];
        }

        $extracted = $this->extractTagsFromMarkdown($markdown);
        $tags = array_unique(array_merge($tags, $extracted));
        if (empty($tags)) {
            $tags = ['untagged'];
        }
        if (count($tags) > 1 && in_array('untagged', $tags, true)) {
            $tags = array_diff($tags, ['untagged']);
        }

        $success = $this->assetRepository->updateNote($id, $title, $markdown);
        if ($success) {
            $this->assetRepository->assignTags($id, $tags);
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Không thể cập nhật ghi chú.'];
    }

    public function deleteAsset(int $id): bool
    {
        // Optional: Could delete the S3 object if type is resource
        return $this->assetRepository->delete($id);
    }

    public function createFragment(array $data): array
    {
        if (!isset($data['asset_id'])) {
            return ['success' => false, 'error' => 'Thiếu Asset ID.'];
        }

        $fragmentId = $this->assetRepository->createFragment($data);
        return ['success' => true, 'id' => $fragmentId];
    }

    public function getAssetFragments(int $assetId): array
    {
        return $this->assetRepository->getFragmentsByAsset($assetId);
    }

    public function getAssetUrl(string $storageKey): string
    {
        return Storage::getDownloadUrl($storageKey);
    }

    public function getRelatedAssets(int $flowId, string $tag): array
    {
        return $this->assetRepository->getRelatedAssets($flowId, $tag);
    }

    public function updateAssetTags(int $assetId, array $tags): void
    {
        $this->assetRepository->assignTags($assetId, $tags);
    }

    public function createFolder(int $studyflowId, string $title, ?string $description = null): array
    {
        if (trim($title) === '') {
            return ['success' => false, 'error' => 'Tên thư mục không được để trống.'];
        }

        try {
            $folderId = $this->assetRepository->createFolder([
                'studyflow_id' => $studyflowId,
                'title' => trim($title),
                'description' => $description
            ]);
            return ['success' => true, 'id' => $folderId];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateFolderName(int $id, string $newName): array
    {
        if (trim($newName) === '') {
            return ['success' => false, 'error' => 'Tên thư mục không được để trống.'];
        }

        $success = $this->assetRepository->updateFolderName($id, trim($newName));
        if ($success) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Không thể đổi tên thư mục.'];
    }

    public function updateAssetTitle(int $id, string $newTitle): array
    {
        if (trim($newTitle) === '') {
            return ['success' => false, 'error' => 'Tên không được để trống.'];
        }

        $success = $this->assetRepository->updateAssetTitle($id, trim($newTitle));
        if ($success) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Không thể đổi tên tài liệu.'];
    }

    public function moveAsset(int $id, string $folderName): array
    {
        $success = $this->assetRepository->moveAsset($id, trim($folderName));
        if ($success) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Không thể di chuyển tài liệu.'];
    }

    public function reorderAssets(array $orderIds): array
    {
        $success = $this->assetRepository->updateAssetOrder($orderIds);
        if ($success) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Không thể sắp xếp tài liệu.'];
    }
}
