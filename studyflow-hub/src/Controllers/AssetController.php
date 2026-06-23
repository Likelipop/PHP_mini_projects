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
        // Early check: if post_max_size was exceeded, $_POST and $_FILES will be empty
        // This must be checked BEFORE CsrfMiddleware which would fail with a text 403
        if (empty($_POST) && empty($_FILES)) {
            $this->json(413, [
                'success' => false, 
                'error' => 'Kích thước yêu cầu vượt quá giới hạn máy chủ (post_max_size). Hãy tải lên file nhỏ hơn.'
            ]);
            return;
        }

        CsrfMiddleware::handle();
        $studyflowId = (int)Request::input('studyflow_id');
        $file = Request::file('file');
        $folder = Request::input('folder_name', 'Root');
        $tagsString = Request::input('tags', '');
        $title = Request::input('title', '');
        
        if (!$file || !isset($file['error'])) {
            $this->json(400, ['success' => false, 'error' => 'Không nhận được tệp tin.']);
            return;
        }

        // Handle specific PHP upload error codes
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE   => 'Tệp vượt quá giới hạn upload_max_filesize (' . ini_get('upload_max_filesize') . ').',
                UPLOAD_ERR_FORM_SIZE  => 'Tệp vượt quá giới hạn MAX_FILE_SIZE trong form.',
                UPLOAD_ERR_PARTIAL    => 'Tệp chỉ được tải lên một phần. Vui lòng thử lại.',
                UPLOAD_ERR_NO_FILE    => 'Không có tệp tin nào được tải lên.',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm trên máy chủ.',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi tệp tin lên đĩa.',
                UPLOAD_ERR_EXTENSION  => 'Một extension PHP đã dừng việc tải tệp.',
            ];
            $msg = $errorMessages[$file['error']] ?? 'Lỗi tải lên không xác định (code: ' . $file['error'] . ').';
            $this->json(400, ['success' => false, 'error' => $msg]);
            return;
        }

        if ($file['size'] > 26214400) { // 25MB limit
            $this->json(400, ['success' => false, 'error' => 'Kích thước tệp vượt quá 25MB.']);
            return;
        }

        // If title is empty, use filename
        if (trim($title) === '') {
            $title = pathinfo($file['name'], PATHINFO_FILENAME);
        }

        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : [];
        if (empty($tags)) {
            // Default to title as tag
            $tags = [strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($title)))];
        }

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
            $asset['file_size'] = $file['size'];

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

    public function getRelatedAssetsApi(): void
    {
        $flowId = (int)Request::input('flow_id');
        $tag = Request::input('tag', '');
        
        $results = $this->assetService->getRelatedAssets($flowId, $tag);
        $this->json(200, $results);
    }

    public function updateAssetTagsApi(string $id): void
    {
        CsrfMiddleware::handle();
        $tagsString = Request::input('tags', '');
        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : [];
        if (empty($tags)) {
            $tags = ['untagged'];
        }
        
        $this->assetService->updateAssetTags((int)$id, $tags);
        $this->json(200, ['success' => true]);
    }

    public function makeFolderApi(): void
    {
        CsrfMiddleware::handle();
        $studyflowId = (int)Request::input('studyflow_id');
        $title = Request::input('title', '');
        $description = Request::input('description', null);
        
        $result = $this->assetService->createFolder($studyflowId, $title, $description);
        $this->json($result['success'] ? 200 : 400, $result);
    }

    public function renameFolderApi(string $id): void
    {
        CsrfMiddleware::handle();
        $newName = Request::input('new_name', '');
        
        $result = $this->assetService->updateFolderName((int)$id, $newName);
        $this->json($result['success'] ? 200 : 400, $result);
    }

    public function renameAssetApi(string $id): void
    {
        CsrfMiddleware::handle();
        $newTitle = Request::input('new_title', '');
        
        $result = $this->assetService->updateAssetTitle((int)$id, $newTitle);
        $this->json($result['success'] ? 200 : 400, $result);
    }

    public function moveAssetApi(string $id): void
    {
        CsrfMiddleware::handle();
        $folderName = Request::input('folder_name', '');
        
        $result = $this->assetService->moveAsset((int)$id, $folderName);
        $this->json($result['success'] ? 200 : 400, $result);
    }

    public function reorderAssetsApi(): void
    {
        CsrfMiddleware::handle();
        $order = json_decode(Request::input('order', '[]'), true);
        
        if (!is_array($order) || empty($order)) {
            $this->json(400, ['success' => false, 'error' => 'Danh sách sắp xếp không hợp lệ.']);
            return;
        }

        $result = $this->assetService->reorderAssets($order);
        $this->json($result['success'] ? 200 : 400, $result);
    }
}
