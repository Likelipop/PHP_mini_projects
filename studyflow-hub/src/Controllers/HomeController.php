<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Services\StudyFlowService;
use StudyFlow\Core\Session;
use StudyFlow\Core\Response;
use StudyFlow\Core\Database;

class HomeController extends BaseController
{
    private StudyFlowService $studyFlowService;

    public function __construct()
    {
        parent::__construct();
        $this->studyFlowService = new StudyFlowService();
    }

    public function index(): void
    {
        // 1. Fetch studyflows
        $trending = $this->studyFlowService->getTrendingStudyFlows(4);
        
        $paginated = $this->studyFlowService->getPaginatedStudyFlows('', 'created_at', 'desc', 1, 6);
        $newest = $paginated['items'] ?? [];

        // Build list of popular tags mock (or active tags if we have helper)
        $popularTags = [
            ['name' => 'Machine Learning', 'prefix' => 'machine-learning'],
            ['name' => 'CNN', 'prefix' => 'machine-learning/cnn'],
            ['name' => 'Transformer', 'prefix' => 'machine-learning/transformer'],
            ['name' => 'Obsidian', 'prefix' => 'obsidian'],
            ['name' => 'Deep Learning', 'prefix' => 'deep-learning']
        ];

        $this->render('home', [
            'trending' => $trending,
            'newest' => $newest,
            'popularTags' => $popularTags,
            'success' => flash_get('success'),
            'error' => flash_get('error'),
        ]);
    }

    public function profile(): void
    {
        Session::requireLogin();
        
        $userService = new \StudyFlow\Services\UserService();
        $stats = $userService->getUserStats((int)Session::get('user_id'));
        
        $this->render('profile', [
            'stats' => $stats,
            'success' => flash_get('success'),
            'error' => flash_get('error'),
        ]);
    }

    public function notifications(): void
    {
        Session::requireLogin();
        
        // Return a few mock notification items for HTMX dropdown body
        $notifs = [
            [
                'icon' => 'fa-solid fa-circle-plus text-success',
                'text' => 'Nguyễn Văn A đã tải lên <code>Lecture3.pdf</code>',
                'time' => '3 phút trước'
            ],
            [
                'icon' => 'fa-solid fa-pen text-warning',
                'text' => 'Trần Thị B đã sửa ghi chú <code>Decision Tree</code>',
                'time' => '25 phút trước'
            ],
            [
                'icon' => 'fa-solid fa-link text-info',
                'text' => 'Hệ thống đã liên kết tag <code>Transformer</code>',
                'time' => '2 giờ trước'
            ]
        ];

        // Send HTML output directly back
        $html = '';
        foreach ($notifs as $n) {
            $html .= '<div class="list-group-item d-flex align-items-start gap-2 py-2.5 border-light-subtle">
                <span class="' . $n['icon'] . ' mt-0.5 small"></span>
                <div class="flex-grow-1">
                    <span class="xsmall text-body">' . $n['text'] . '</span>
                    <div class="text-muted xsmall font-monospace" style="font-size: 0.75rem;">' . $n['time'] . '</div>
                </div>
            </div>';
        }
        
        // Trigger a custom client header to illuminate notification dot
        header('HX-Trigger: {"newNotifications": true}');
        echo $html;
        exit;
    }

    public function searchApi(): void
    {
        $query = \StudyFlow\Core\Request::input('q', '');
        $assetRepo = new \StudyFlow\Repositories\AssetRepository();
        $results = $assetRepo->searchEverywhere($query);
        $this->json(200, $results);
    }

    public function health(): void
    {
        try {
            $db = Database::getConnection();
            $db->query('SELECT 1');
            Response::json(200, ['status' => 'ok', 'database' => 'connected']);
        } catch (\Exception $e) {
            Response::json(500, ['status' => 'error', 'database' => 'disconnected', 'error' => $e->getMessage()]);
        }
    }
}
