<script>
    window.resourcesList = <?= json_encode($resources ?? []) ?>;
</script>
<div class="workspace-container d-flex flex-column" data-flow-id="<?= $flow['id'] ?>" data-flow-slug="<?= h($flow['slug']) ?>" x-data="{ activeSection: 'readme' }">
    <!-- Top Workspace Header -->
    <header class="border-bottom px-4 py-3 bg-body-tertiary">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 xsmall font-monospace">
                        <li class="breadcrumb-item"><a href="/studyflows" class="text-decoration-none">StudyFlows</a></li>
                        <li class="breadcrumb-item active" aria-current="page">/<?= h($flow['slug']) ?></li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold mb-0" style="font-family: var(--font-heading);"><?= h($flow['title']) ?></h1>
                    <?php if ($flow['is_pinned']): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle small"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
                    <?php endif; ?>
                    <span class="badge bg-secondary-subtle text-secondary border small font-monospace">owner: <?= h($flow['username'] ?? 'owner') ?></span>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <!-- Alpine section switch buttons (obsidian/vscode sidebar navigation tabs) -->
                <button class="btn btn-sm btn-outline-secondary" :class="activeSection === 'readme' ? 'active' : ''" @click="activeSection = 'readme'">
                    <i class="fa-solid fa-book-open me-1"></i> README
                </button>
                <button class="btn btn-sm btn-outline-secondary" :class="activeSection === 'workspace' ? 'active' : ''" @click="activeSection = 'workspace'">
                    <i class="fa-solid fa-laptop-code me-1"></i> Workspace
                </button>
                <?php if ((int)$flow['user_id'] === (int)$_SESSION['user_id']): ?>
                    <form action="/studyflow/<?= h($flow['slug']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa StudyFlow này?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash-can"></i> Xóa Flow</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Side-by-side workspace panels (VSCode UI style) -->
    <div class="flex-grow-1 row g-0">
        <!-- Sidebar Navigation (Readme & Tree collapses) -->
        <aside class="col-lg-3 border-end bg-body-tertiary p-3 d-flex flex-column gap-3" style="max-height: calc(100vh - 140px); overflow-y: auto;">
            <!-- TagBench Breadcrumb Tree Component -->
            <?php require __DIR__ . '/../../components/tagbench.php'; ?>

            <!-- Sidebar section lists (VSCode like folders structure) -->
            <?php require __DIR__ . '/../../components/sidebar.php'; ?>
        </aside>

        <!-- Content Area -->
        <main class="col-lg-9 d-flex flex-column" style="max-height: calc(100vh - 140px); overflow-y: auto;">
            <!-- README View Page -->
            <div x-show="activeSection === 'readme'" class="p-4" x-transition>
                <div class="card shadow-sm border border-secondary-subtle">
                    <div class="card-header bg-body-tertiary fw-bold py-3">
                        <i class="fa-solid fa-book me-2"></i> README.md
                    </div>
                    <div class="card-body p-4 markdown-rendered" id="flow-readme-container">
                        <!-- Server side parsedown render -->
                        <?php
                        $parsedown = new \Parsedown();
                        $readme = $flow['description'] ?: "# " . $flow['title'] . "\n\nKhông có tài liệu mô tả cho StudyFlow này.";
                        echo $parsedown->text($readme);
                        ?>
                    </div>
                </div>
            </div>

            <!-- Workspace tab panes -->
            <div x-show="activeSection === 'workspace'" class="p-3 flex-grow-1 d-flex flex-column" x-transition>
                <!-- Bootstrap Tabs for Resources vs Notes -->
                <ul class="nav nav-tabs mb-3" id="workspaceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-body small" id="resources-tab" data-bs-toggle="tab" data-bs-target="#tab-resources" type="button" role="tab" aria-controls="tab-resources" aria-selected="true">
                            <i class="fa-solid fa-file-pdf me-1 text-primary"></i> Resources (Tài nguyên)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-body small" id="notes-tab" data-bs-toggle="tab" data-bs-target="#tab-notes" type="button" role="tab" aria-controls="tab-notes" aria-selected="false">
                            <i class="fa-solid fa-note-sticky me-1 text-warning"></i> Notes (Ghi chú)
                        </button>
                    </li>
                </ul>

                <div class="tab-content flex-grow-1 d-flex flex-column" id="workspaceTabsContent">
                    <!-- Resources Panel Component -->
                    <div class="tab-pane fade show active flex-grow-1" id="tab-resources" role="tabpanel" aria-labelledby="resources-tab">
                        <?php require __DIR__ . '/../../components/resource_grid.php'; ?>
                    </div>

                    <!-- Notes Panel Component -->
                    <div class="tab-pane fade flex-grow-1" id="tab-notes" role="tabpanel" aria-labelledby="notes-tab">
                        <?php require __DIR__ . '/../../components/note_editor.php'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
