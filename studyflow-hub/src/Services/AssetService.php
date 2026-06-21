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

    public function createNote(int $studyflowId, string $title, string $markdown, array $tags = []): array
    {
        $title = trim($title);
        if ($title === '') {
            return ['success' => false, 'error' => 'Tiêu đề ghi chú không được để trống.'];
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
}
