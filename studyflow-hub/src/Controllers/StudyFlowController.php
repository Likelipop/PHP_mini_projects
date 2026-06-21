<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Services\StudyFlowService;
use StudyFlow\Services\AssetService;
use StudyFlow\Services\TagService;
use StudyFlow\Core\Session;
use StudyFlow\Core\Request;
use StudyFlow\Core\Response;
use StudyFlow\Core\Middleware\CsrfMiddleware;
use StudyFlow\Core\Middleware\HoneypotMiddleware;

class StudyFlowController extends BaseController
{
    private StudyFlowService $studyFlowService;
    private AssetService $assetService;
    private TagService $tagService;

    public function __construct()
    {
        parent::__construct();
        // Require auth for all studyflow actions
        Session::requireLogin();
        
        $this->studyFlowService = new StudyFlowService();
        $this->assetService = new AssetService();
        $this->tagService = new TagService();
    }

    public function index(): void
    {
        $search = Request::input('search', '');
        $sortBy = Request::input('sort_by', 'created_at');
        $sortDir = Request::input('sort_dir', 'desc');
        $page = (int)Request::input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginated = $this->studyFlowService->getPaginatedStudyFlows($search, $sortBy, $sortDir, $page, 6);

        $this->render('studyflow/index', [
            'flows' => $paginated['items'],
            'total' => $paginated['total'],
            'page' => $paginated['page'],
            'total_pages' => $paginated['total_pages'],
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'success' => flash_get('success'),
            'error' => flash_get('error'),
        ]);
    }

    public function showCreate(): void
    {
        $this->render('studyflow/create', [
            'errors' => flash_get('errors', []),
            'old' => flash_get('old', []),
        ]);
    }

    public function create(): void
    {
        HoneypotMiddleware::handle();
        CsrfMiddleware::handle();

        $data = [
            'user_id' => Session::get('user_id'),
            'title' => Request::input('title', ''),
            'slug' => Request::input('slug', ''),
            'description' => Request::input('description', ''),
            'is_pinned' => Request::input('is_pinned') === '1',
            'is_public' => Request::input('is_public') === '1',
        ];

        $result = $this->studyFlowService->createStudyFlow($data);

        if ($result['success']) {
            flash_set('success', 'Tạo StudyFlow thành công!');
            $this->redirect('/studyflow/' . $result['slug']);
        } else {
            flash_set('errors', $result['errors']);
            flash_set('old', $data);
            $this->redirect('/studyflows/create');
        }
    }

    public function show(string $slug): void
    {
        $flow = $this->studyFlowService->getStudyFlowBySlug($slug);
        
        if (!$flow) {
            Response::notFound('StudyFlow not found');
            return;
        }

        // Fetch active filters
        $tagFilter = Request::input('tag', '');
        $searchFilter = Request::input('search', '');
        $folderFilter = Request::input('folder', '');

        $assets = $this->assetService->getAssets((int)$flow['id'], [
            'tag' => $tagFilter,
            'search' => $searchFilter,
            'folder' => $folderFilter,
        ]);

        $tags = $this->tagService->getTagsForStudyFlow((int)$flow['id']);

        // Group assets into notes and resources
        $notes = [];
        $resources = [];
        $resourcesByFolder = [];

        foreach ($assets as $asset) {
            // Simplify tag structures into flat array of prefixes for easy JS checking
            $flatTags = [];
            if (isset($asset['tags'])) {
                foreach ($asset['tags'] as $t) {
                    $flatTags[] = $t['prefix'];
                }
            }
            $asset['tags'] = $flatTags;

            if ($asset['type'] === 'note') {
                $notes[] = $asset;
            } else {
                $folder = $asset['folder_name'] ?: 'Root';
                
                // Hydrate presigned S3/MinIO download URLs
                $asset['presigned_url'] = $this->assetService->getAssetUrl($asset['storage_key']);
                
                // Determine file type
                $mime = strtolower($asset['mime_type'] ?? '');
                if (str_contains($mime, 'image/')) {
                    $asset['file_type'] = 'image';
                } elseif ($mime === 'application/pdf' || str_ends_with(strtolower($asset['filename'] ?? ''), '.pdf')) {
                    $asset['file_type'] = 'pdf';
                } else {
                    $asset['file_type'] = 'other';
                }
                
                $asset['file_size'] = 1420000; // Mock file size in bytes (approx 1.4 MB)
                
                $resources[] = $asset;
                $resourcesByFolder[$folder][] = $asset;
            }
        }

        $this->render('studyflow/show', [
            'flow' => $flow,
            'notes' => $notes,
            'resources' => $resources,
            'resourcesByFolder' => $resourcesByFolder,
            'tags' => $tags,
            'active_tag' => $tagFilter,
            'active_search' => $searchFilter,
            'active_folder' => $folderFilter,
            'success' => flash_get('success'),
            'error' => flash_get('error'),
        ]);
    }

    public function delete(string $slug): void
    {
        CsrfMiddleware::handle();
        $flow = $this->studyFlowService->getStudyFlowBySlug($slug);
        
        if (!$flow) {
            Response::notFound();
            return;
        }

        // Only owner can delete (or check if user_id matches)
        if ((int)$flow['user_id'] !== (int)Session::get('user_id')) {
            flash_set('error', 'Bạn không có quyền xóa StudyFlow này.');
            $this->redirect('/studyflow/' . $slug);
            return;
        }

        $this->studyFlowService->deleteStudyFlow((int)$flow['id']);
        flash_set('success', 'Đã xóa StudyFlow thành công.');
        $this->redirect('/studyflows');
    }
}
