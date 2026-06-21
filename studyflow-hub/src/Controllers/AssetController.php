<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Services\AssetService;
use StudyFlow\Services\StudyFlowService;
use StudyFlow\Services\TagService;
use StudyFlow\Core\Request;
use StudyFlow\Core\Response;
use StudyFlow\Core\Session;
use StudyFlow\Core\Middleware\CsrfMiddleware;

class AssetController extends BaseController
{
    private AssetService $assetService;
    private StudyFlowService $studyFlowService;
    private TagService $tagService;

    public function __construct()
    {
        parent::__construct();
        Session::requireLogin();
        
        $this->assetService = new AssetService();
        $this->studyFlowService = new StudyFlowService();
        $this->tagService = new TagService();
    }

    public function uploadApi(): void
    {
        CsrfMiddleware::handle();
        $studyflowId = (int)Request::input('studyflow_id');
        $file = Request::file('file');
        $folder = Request::input('folder_name', 'Root');
        $tagsString = Request::input('tags', '');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(400, ['success' => false, 'error' => 'Tệp tải lên không hợp lệ hoặc bị lỗi.']);
            return;
        }

        // Auto categorize folder based on filename extension
        $filename = basename($file['name']);
        if (str_ends_with(strtolower($filename), '.pdf')) {
            $folder = 'Slides';
        } elseif (preg_match('/\.(png|jpg|jpeg|gif|svg)$/i', $filename)) {
            $folder = 'Images';
        } else {
            $folder = 'Assignments';
        }

        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : ['untagged'];

        $result = $this->assetService->createResource($studyflowId, $file, $folder, $tags);

        if ($result['success']) {
            $asset = $this->assetService->getAssetById((int)$result['id']);
            $asset['presigned_url'] = $this->assetService->getAssetUrl($asset['storage_key']);
            $asset['tags'] = $tags;
            
            // Determine file_type
            $mime = strtolower($asset['mime_type'] ?? '');
            if (str_contains($mime, 'image/')) {
                $asset['file_type'] = 'image';
            } elseif ($mime === 'application/pdf' || str_ends_with(strtolower($asset['filename'] ?? ''), '.pdf')) {
                $asset['file_type'] = 'pdf';
            } else {
                $asset['file_type'] = 'other';
            }
            $asset['file_size'] = 1420000;

            $this->json(200, ['success' => true, 'asset' => $asset]);
        } else {
            $this->json(400, ['success' => false, 'error' => $result['error']]);
        }
    }

    public function createNoteApi(): void
    {
        CsrfMiddleware::handle();
        $studyflowId = (int)Request::input('studyflow_id');
        $title = Request::input('title', 'Ghi chú mới');
        $markdown = Request::input('content', '');
        $tagsString = Request::input('tags', 'untagged');
        $tags = array_map('trim', explode(',', $tagsString));

        $result = $this->assetService->createNote($studyflowId, $title, $markdown, $tags);

        if ($result['success']) {
            $note = $this->assetService->getAssetById((int)$result['id']);
            $note['tags'] = $tags;
            $this->json(200, ['success' => true, 'note' => $note]);
        } else {
            $this->json(400, ['success' => false, 'error' => $result['error']]);
        }
    }

    public function saveNoteApi(string $id): void
    {
        CsrfMiddleware::handle();
        $title = Request::input('title', 'Ghi chú');
        $markdown = Request::input('content', '');
        $tagsString = Request::input('tags', 'untagged');
        $tags = array_map('trim', explode(',', $tagsString));

        $result = $this->assetService->updateNote((int)$id, $title, $markdown, $tags);

        if ($result['success']) {
            $this->json(200, ['success' => true]);
        } else {
            $this->json(400, ['success' => false, 'error' => $result['error']]);
        }
    }

    public function deleteAssetApi(string $id): void
    {
        CsrfMiddleware::handle();
        $success = $this->assetService->deleteAsset((int)$id);
        $this->json(200, ['success' => $success]);
    }

    public function download(string $id): void
    {
        $asset = $this->assetService->getAssetById((int)$id);
        
        if (!$asset || $asset['type'] !== 'resource') {
            Response::notFound('File not found');
            return;
        }

        $url = $this->assetService->getAssetUrl($asset['storage_key']);
        $this->redirect($url);
    }

    public function searchTags(): void
    {
        $query = Request::input('q', '');
        $tags = $this->tagService->searchTags($query);
        $this->json(200, $tags);
    }

    public function createFragmentApi(): void
    {
        CsrfMiddleware::handle();
        $data = [
            'asset_id' => (int)Request::input('asset_id'),
            'tag_name' => Request::input('tag_prefix', ''),
            'page' => Request::input('page_number') !== null ? (int)Request::input('page_number') : null,
            'bbox' => Request::input('bounding_box', ''),
            'text' => Request::input('note', ''),
            'image_path' => Request::input('image_path', ''),
        ];

        $result = $this->assetService->createFragment($data);
        if ($result['success']) {
            $this->json(200, ['success' => true, 'id' => $result['id']]);
        } else {
            $this->json(400, ['success' => false, 'error' => $result['error']]);
        }
    }

    public function getFragmentsApi(string $id): void
    {
        $fragments = $this->assetService->getAssetFragments((int)$id);
        
        foreach ($fragments as &$fragment) {
            if ($fragment['image_path']) {
                $fragment['image_url'] = $this->assetService->getAssetUrl($fragment['image_path']);
            }
        }
        
        $this->json(200, $fragments);
    }
}
