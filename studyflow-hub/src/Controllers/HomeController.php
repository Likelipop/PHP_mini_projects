<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Services\StudyFlowService;
use StudyFlow\Core\Session;

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
}
