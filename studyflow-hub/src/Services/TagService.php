<?php

declare(strict_types=1);

namespace StudyFlow\Services;

use StudyFlow\Repositories\TagRepository;

class TagService
{
    private TagRepository $tagRepository;

    public function __construct()
    {
        $this->tagRepository = new TagRepository();
    }

    public function getTagsForStudyFlow(int $studyflowId): array
    {
        return $this->tagRepository->getAllTagsForStudyFlow($studyflowId);
    }

    public function searchTags(string $query): array
    {
        return $this->tagRepository->searchTags($query);
    }
}
