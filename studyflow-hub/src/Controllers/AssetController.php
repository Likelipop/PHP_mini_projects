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

    public function upload(string $slug): void
    {
        CsrfMiddleware::handle();
        $flow = $this->studyFlowService->getStudyFlowBySlug($slug);
        
        if (!$flow) {
            Response::notFound('StudyFlow not found');
            return;
        }

        $file = Request::file('resource_file');
        $folder = Request::input('folder_name', 'Root');
        $tagsString = Request::input('tags', '');
        
        // Parse comma separated tags
        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : ['untagged'];

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', 'Tệp tải lên không hợp lệ hoặc bị lỗi.');
            $this->redirect('/studyflow/' . $slug);
            return;
        }

        $result = $this->assetService->createResource((int)$flow['id'], $file, $folder, $tags);

        if ($result['success']) {
            flash_set('success', 'Tải tài liệu lên thành công!');
        } else {
            flash_set('error', $result['error']);
        }

        $this->redirect('/studyflow/' . $slug);
    }

    public function createNote(string $slug): void
    {
        CsrfMiddleware::handle();
        $flow = $this->studyFlowService->getStudyFlowBySlug($slug);
        
        if (!$flow) {
            Response::notFound();
            return;
        }

        $title = Request::input('title', '');
        $markdown = Request::input('markdown', '');
        $tagsString = Request::input('tags', '');
        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : ['untagged'];

        $result = $this->assetService->createNote((int)$flow['id'], $title, $markdown, $tags);

        if ($result['success']) {
            flash_set('success', 'Tạo ghi chú thành công!');
        } else {
            flash_set('error', $result['error']);
        }

        $this->redirect('/studyflow/' . $slug);
    }

    public function editNote(string $slug, string $id): void
    {
        CsrfMiddleware::handle();
        
        $title = Request::input('title', '');
        $markdown = Request::input('markdown', '');
        $tagsString = Request::input('tags', '');
        $tags = $tagsString !== '' ? array_map('trim', explode(',', $tagsString)) : ['untagged'];

        $result = $this->assetService->updateNote((int)$id, $title, $markdown, $tags);

        if ($result['success']) {
            flash_set('success', 'Cập nhật ghi chú thành công!');
        } else {
            flash_set('error', $result['error']);
        }

        $this->redirect('/studyflow/' . $slug);
    }

    public function deleteAsset(string $slug, string $id): void
    {
        CsrfMiddleware::handle();
        $this->assetService->deleteAsset((int)$id);
        flash_set('success', 'Đã xóa tài nguyên thành công.');
        $this->redirect('/studyflow/' . $slug);
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
        $data = [
            'asset_id' => (int)Request::input('asset_id'),
            'tag_name' => Request::input('tag_name', ''),
            'page' => Request::input('page') !== null ? (int)Request::input('page') : null,
            'bbox' => Request::input('bbox', ''),
            'text' => Request::input('text', ''),
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
        
        // Populate download urls if sub-images exist
        foreach ($fragments as &$fragment) {
            if ($fragment['image_path']) {
                $fragment['image_url'] = $this->assetService->getAssetUrl($fragment['image_path']);
            }
        }
        
        $this->json(200, $fragments);
    }
}
