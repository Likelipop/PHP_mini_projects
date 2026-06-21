<?php

declare(strict_types=1);

namespace StudyFlow\Services;

use StudyFlow\Repositories\StudyFlowRepository;

class StudyFlowService
{
    private StudyFlowRepository $studyFlowRepository;

    public function __construct()
    {
        $this->studyFlowRepository = new StudyFlowRepository();
    }

    public function getStudyFlowById(int $id): ?array
    {
        return $this->studyFlowRepository->findById($id);
    }

    public function getStudyFlowBySlug(string $slug): ?array
    {
        return $this->studyFlowRepository->findBySlug($slug);
    }

    public function createStudyFlow(array $data): array
    {
        $errors = [];
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($title === '') {
            $errors['title'] = 'Tiêu đề StudyFlow không được để trống.';
        }

        if ($slug === '') {
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }

        if ($slug === '') {
            $errors['slug'] = 'Đường dẫn (slug) không hợp lệ.';
        } elseif ($this->studyFlowRepository->findBySlug($slug)) {
            $errors['slug'] = 'Đường dẫn (slug) này đã được sử dụng. Vui lòng chọn tên khác.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $flowId = $this->studyFlowRepository->create([
            'user_id' => $data['user_id'] ?? null,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_public' => $data['is_public'] ?? true,
        ]);

        return ['success' => true, 'id' => $flowId, 'slug' => $slug];
    }

    public function updateStudyFlow(int $id, array $data): array
    {
        $errors = [];
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($title === '') {
            $errors['title'] = 'Tiêu đề StudyFlow không được để trống.';
        }

        if ($slug === '') {
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }

        $existing = $this->studyFlowRepository->findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
            $errors['slug'] = 'Đường dẫn (slug) này đã được sử dụng bởi StudyFlow khác.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->studyFlowRepository->update($id, [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_public' => $data['is_public'] ?? true,
        ]);

        return ['success' => true, 'slug' => $slug];
    }

    public function deleteStudyFlow(int $id): bool
    {
        return $this->studyFlowRepository->delete($id);
    }

    public function getPaginatedStudyFlows(string $search = '', string $sortBy = 'created_at', string $sortDir = 'desc', int $page = 1, int $perPage = 6): array
    {
        return $this->studyFlowRepository->getPaginated($search, $sortBy, $sortDir, $page, $perPage);
    }

    public function getTrendingStudyFlows(int $limit = 4): array
    {
        return $this->studyFlowRepository->getTrending($limit);
    }

    private function slugify(string $text): string
    {
        // Simple slugify helper
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text;
    }
}
