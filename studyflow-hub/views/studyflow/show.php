<div class="workspace-wrapper" data-flow-id="<?= $flow['id'] ?>" data-flow-slug="<?= h($flow['slug']) ?>">
    <!-- Workspace Sidebar (Left column: TagBench) -->
    <aside class="workspace-sidebar">
        <div class="tagbench-header">
            <h3 class="tagbench-title"><i class="fa-solid fa-folder-tree"></i> TagBench</h3>
            <span class="tagbench-subtitle">Obsidian Tag + Database</span>
        </div>

        <div class="tagbench-search">
            <div class="input-wrapper">
                <i class="fa-solid fa-magnifying-glass input-icon"></i>
                <input type="search" id="tagbench-filter" class="form-control form-control-sm" placeholder="Tìm nhanh tag...">
            </div>
            <!-- Autocomplete list -->
            <ul id="tagbench-autocomplete" class="tagbench-autocomplete-list" style="display: none;"></ul>
        </div>

        <div class="tagbench-tree-container">
            <div class="tag-tree-node root-node">
                <a href="/studyflow/<?= h($flow['slug']) ?>" class="tag-tree-link <?= $active_tag === '' ? 'active' : '' ?>">
                    <i class="fa-solid fa-cube"></i> Tất cả Assets
                </a>
            </div>
            
            <div class="tag-tree-node root-node">
                <a href="/studyflow/<?= h($flow['slug']) ?>?tag=untagged" class="tag-tree-link <?= $active_tag === 'untagged' ? 'active' : '' ?>">
                    <i class="fa-solid fa-tag"></i> Untagged (Mặc định)
                </a>
            </div>

            <!-- Hierarchical Tree rendering -->
            <div class="tag-tree-hierarchy" id="tag-tree-root">
                <!-- Javascript will render the collapsible tag graph here or PHP fallback -->
                <?php
                // Build hierarchical structure from tag list
                $tree = [];
                foreach ($tags as $tag) {
                    $prefix = $tag['prefix'];
                    if ($prefix === 'untagged') continue;
                    $parts = explode('/', $prefix);
                    $current = &$tree;
                    foreach ($parts as $part) {
                        if (!isset($current[$part])) {
                            $current[$part] = [
                                'name' => $part,
                                'full_prefix' => (isset($current['full_prefix']) ? $current['full_prefix'] . '/' : '') . $part,
                                'children' => []
                            ];
                        }
                        // Re-establish parent full prefix path
                        $current[$part]['full_prefix'] = (isset($current['full_prefix']) && $current['full_prefix'] !== '' ? $current['full_prefix'] . '/' : '') . $part;
                        $current = &$current[$part]['children'];
                    }
                }

                // Recursive function to render tree
                function renderTagTree(array $nodes, string $activeTag, string $slug): void {
                    echo '<ul class="tag-tree-list">';
                    foreach ($nodes as $name => $node) {
                        $isCurrentActive = strtolower($activeTag) === strtolower($node['full_prefix']);
                        $hasChildren = !empty($node['children']);
                        echo '<li class="tag-tree-item">';
                        echo '<div class="tag-tree-row">';
                        if ($hasChildren) {
                            echo '<button class="tag-tree-toggle"><i class="fa-solid fa-caret-down"></i></button>';
                        } else {
                            echo '<span class="tag-tree-indent"></span>';
                        }
                        echo '<a href="/studyflow/' . h($slug) . '?tag=' . urlencode($node['full_prefix']) . '" class="tag-tree-link ' . ($isCurrentActive ? 'active' : '') . '">';
                        echo '<i class="fa-solid fa-hashtag tag-icon"></i> ' . h($node['name']);
                        echo '</a>';
                        echo '</div>';
                        if ($hasChildren) {
                            renderTagTree($node['children'], $activeTag, $slug);
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                }

                renderTagTree($tree, $active_tag, $flow['slug']);
                ?>
            </div>
        </div>

        <div class="sidebar-footer-info">
            <h4 class="sidebar-section-title">Thư mục con (1 Cấp)</h4>
            <ul class="folder-list">
                <li>
                    <a href="/studyflow/<?= h($flow['slug']) ?>?folder=Slides" class="folder-link <?= $active_folder === 'Slides' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-powerpoint"></i> Slides
                    </a>
                </li>
                <li>
                    <a href="/studyflow/<?= h($flow['slug']) ?>?folder=Images" class="folder-link <?= $active_folder === 'Images' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-image"></i> Images
                    </a>
                </li>
                <li>
                    <a href="/studyflow/<?= h($flow['slug']) ?>?folder=Assignments" class="folder-link <?= $active_folder === 'Assignments' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-signature"></i> Assignments
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Central Workspace (Right column: Tabs) -->
    <div class="workspace-main">
        <header class="workspace-header">
            <div class="header-meta-actions">
                <span class="badge badge-pinned"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
                <span class="badge badge-slug">slug: <?= h($flow['slug']) ?></span>
                
                <?php if ((int)$flow['user_id'] === (int)Session::get('user_id')): ?>
                    <form action="/studyflow/<?= h($flow['slug']) ?>/delete" method="POST" class="delete-flow-form inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa StudyFlow này và toàn bộ tài nguyên?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-xs btn-outline btn-danger"><i class="fa-solid fa-trash-can"></i> Xóa Flow</button>
                    </form>
                <?php endif; ?>
            </div>
            <h1 class="workspace-title"><?= h($flow['title']) ?></h1>
            <p class="workspace-desc"><?= h($flow['description']) ?></p>
        </header>

        <!-- Main Workspace Tabs -->
        <div class="workspace-tabs">
            <nav class="tabs-nav">
                <button class="tab-btn active" data-tab="tab-readme"><i class="fa-solid fa-book-open"></i> README</button>
                <button class="tab-btn" data-tab="tab-resources"><i class="fa-solid fa-file-lines"></i> Resources (Files)</button>
                <button class="tab-btn" data-tab="tab-notes"><i class="fa-solid fa-note-sticky"></i> Notes (Markdown)</button>
            </nav>

            <div class="tabs-content">
                <!-- Tab README -->
                <div class="tab-pane active" id="tab-readme">
                    <div class="readme-body markdown-rendered">
                        <?php
                        $parsedown = new \Parsedown();
                        $readmeContent = $flow['description'] ?: '# ' . $flow['title'] . "\n\nKhông có nội dung README nào được cung cấp cho StudyFlow này. Thêm mô tả để điền thông tin.";
                        echo $parsedown->text($readmeContent);
                        ?>
                    </div>
                </div>

                <!-- Tab Resources -->
                <div class="tab-pane" id="tab-resources">
                    <div class="resources-toolbar">
                        <h3 class="panel-section-title">Quản lý Tài liệu học tập</h3>
                        
                        <!-- Upload Form -->
                        <form action="/studyflow/<?= h($flow['slug']) ?>/assets/upload" method="POST" enctype="multipart/form-data" class="upload-inline-form">
                            <?= csrf_field() ?>
                            
                            <div class="form-row form-row-sm">
                                <div class="form-group flex-2">
                                    <input type="file" name="resource_file" class="form-control form-control-sm" required>
                                </div>
                                <div class="form-group flex-1">
                                    <select name="folder_name" class="form-control form-control-sm">
                                        <option value="Slides">Slides</option>
                                        <option value="Images">Images</option>
                                        <option value="Assignments">Assignments</option>
                                    </select>
                                </div>
                                <div class="form-group flex-2">
                                    <input type="text" name="tags" class="form-control form-control-sm" placeholder="Tags (ngăn cách bằng dấu phẩy)...">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-upload"></i> Tải lên</button>
                            </div>
                        </form>
                    </div>

                    <!-- Drag and Drop upload dropzone -->
                    <div class="upload-dropzone" id="resources-dropzone">
                        <i class="fa-solid fa-cloud-arrow-up dropzone-icon"></i>
                        <p class="dropzone-text">Kéo thả file PDF hoặc ảnh tại đây để tải nhanh lên S3/MinIO</p>
                    </div>

                    <!-- Resource Categories -->
                    <div class="resources-grid-container">
                        <?php
                        $folders = ['Slides', 'Images', 'Assignments'];
                        foreach ($folders as $folder):
                            if (isset($resourcesByFolder[$folder]) && !empty($resourcesByFolder[$folder])):
                        ?>
                            <div class="folder-group-section">
                                <h4 class="folder-group-title"><i class="fa-regular fa-folder-open"></i> <?= $folder ?></h4>
                                <div class="resources-subgrid">
                                    <?php foreach ($resourcesByFolder[$folder] as $res): ?>
                                        <div class="resource-card" data-id="<?= $res['id'] ?>" data-title="<?= h($res['title']) ?>">
                                            <div class="resource-card-icon">
                                                <?php
                                                if ($folder === 'Slides') {
                                                    echo '<i class="fa-solid fa-file-powerpoint slides-color"></i>';
                                                } elseif ($folder === 'Images') {
                                                    echo '<i class="fa-solid fa-file-image image-color"></i>';
                                                } else {
                                                    echo '<i class="fa-solid fa-file-signature assignments-color"></i>';
                                                }
                                                ?>
                                            </div>
                                            <div class="resource-card-details">
                                                <span class="resource-card-name"><?= h($res['title']) ?></span>
                                                <div class="resource-card-tags">
                                                    <?php foreach ($res['tags'] as $t): ?>
                                                        <span class="badge badge-xs">#<?= h($t['name']) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="resource-card-actions">
                                                <!-- Action links -->
                                                <a href="/assets/download/<?= $res['id'] ?>" class="btn-icon-link" target="_blank" title="Tải về"><i class="fa-solid fa-download"></i></a>
                                                <button class="btn-icon-link btn-fragment-maker" title="Tạo Fragment"><i class="fa-solid fa-crop-simple"></i></button>
                                                
                                                <form action="/studyflow/<?= h($flow['slug']) ?>/assets/<?= $res['id'] ?>/delete" method="POST" class="inline-form" onsubmit="return confirm('Xóa tài nguyên này?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-icon-delete"><i class="fa-solid fa-trash-can"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>

                        <?php if (empty($resourcesByFolder)): ?>
                            <div class="empty-state">
                                <i class="fa-regular fa-file-pdf empty-icon"></i>
                                <p>Chưa có file tài nguyên nào. Hãy dùng form tải lên ở trên để bắt đầu!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab Notes -->
                <div class="tab-pane" id="tab-notes">
                    <div class="notes-layout">
                        <!-- Left panel: Notes list -->
                        <div class="notes-sidebar">
                            <button class="btn btn-sm btn-primary btn-block" id="btn-create-note-trigger">
                                <i class="fa-solid fa-plus"></i> Viết Ghi chú mới
                            </button>
                            <div class="notes-list-items">
                                <?php if (!empty($notes)): ?>
                                    <?php foreach ($notes as $note): ?>
                                        <div class="note-list-card" data-id="<?= $note['id'] ?>" data-title="<?= h($note['title']) ?>" data-markdown="<?= h($note['markdown']) ?>" data-tags="<?= h(implode(',', array_column($note['tags'], 'name'))) ?>">
                                            <h4 class="note-list-title"><i class="fa-regular fa-file-lines"></i> <?= h($note['title']) ?></h4>
                                            <div class="note-list-tags">
                                                <?php foreach ($note['tags'] as $t): ?>
                                                    <span class="badge badge-xs">#<?= h($t['name']) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="note-list-actions">
                                                <button class="btn-note-edit-action"><i class="fa-solid fa-pen-to-square"></i> Sửa</button>
                                                <form action="/studyflow/<?= h($flow['slug']) ?>/assets/<?= $note['id'] ?>/delete" method="POST" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa ghi chú này?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-note-delete-action"><i class="fa-solid fa-trash-can"></i> Xóa</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="empty-text">Chưa có ghi chú nào.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right panel: Interactive Editor -->
                        <div class="notes-editor-panel" id="notes-editor-panel" style="display: none;">
                            <form id="note-form" method="POST" action="/studyflow/<?= h($flow['slug']) ?>/notes/create">
                                <?= csrf_field() ?>
                                <input type="hidden" name="note_id" id="note-id-input">
                                
                                <div class="form-group">
                                    <input type="text" name="title" id="note-title" class="form-control note-editor-title" placeholder="Tiêu đề ghi chú..." required>
                                </div>

                                <!-- Editor Toolbar -->
                                <div class="editor-toolbar">
                                    <button type="button" class="tool-btn" data-syntax="**" title="In đậm"><i class="fa-solid fa-bold"></i></button>
                                    <button type="button" class="tool-btn" data-syntax="_" title="In nghiêng"><i class="fa-solid fa-italic"></i></button>
                                    <button type="button" class="tool-btn" data-syntax="`" title="Chèn mã code"><i class="fa-solid fa-code"></i></button>
                                    <button type="button" class="tool-btn" data-syntax="#" title="Tiêu đề heading"><i class="fa-solid fa-heading"></i></button>
                                    <button type="button" class="tool-btn" id="tool-embed-resource" title="Nhúng tài nguyên @"><i class="fa-solid fa-at"></i> Resource</button>
                                    <button type="button" class="tool-btn" id="tool-insert-tag" title="Thêm tag"><i class="fa-solid fa-tag"></i> Tag</button>
                                    
                                    <div class="editor-mode-toggle">
                                        <button type="button" class="mode-btn active" id="editor-write-btn">Write</button>
                                        <button type="button" class="mode-btn" id="editor-preview-btn">Live Preview</button>
                                    </div>
                                </div>

                                <!-- Writing area -->
                                <div class="editor-split-container">
                                    <div class="editor-textarea-wrapper" id="editor-textarea-side">
                                        <textarea name="markdown" id="note-markdown" class="form-control note-editor-textarea" placeholder="Nhập ghi chú markdown tại đây..." required></textarea>
                                    </div>
                                    <div class="editor-preview-wrapper" id="editor-preview-side" style="display: none;">
                                        <!-- Realtime Markdown parsing will happen here -->
                                        <div class="markdown-rendered" id="note-preview-content"></div>
                                    </div>
                                </div>

                                <div class="form-row form-row-sm mt-3">
                                    <div class="form-group flex-3">
                                        <input type="text" name="tags" id="note-tags" class="form-control form-control-sm" placeholder="Tags (cách nhau bởi dấu phẩy)...">
                                    </div>
                                    <div class="flex-1 text-right">
                                        <button type="submit" class="btn btn-sm btn-primary" id="btn-save-note">Lưu ghi chú</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal dialog for Granular Asset Fragment creation -->
<div class="modal-overlay" id="fragment-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-crop-simple"></i> Tạo Asset Fragment</h3>
            <button class="modal-close" id="fragment-modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Trích xuất lát cắt thông tin chi tiết (Fragment) để tag hoặc transclude vào Note.</p>
            <form id="fragment-form">
                <input type="hidden" id="frag-asset-id">
                <div class="form-group">
                    <label class="form-label">Tài nguyên gốc</label>
                    <input type="text" id="frag-asset-title" class="form-control form-control-sm" readonly>
                </div>
                <div class="form-row form-row-sm">
                    <div class="form-group">
                        <label class="form-label" for="frag-page">Số trang (PDF)</label>
                        <input type="number" id="frag-page" class="form-control form-control-sm" placeholder="Ví dụ: 3">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="frag-bbox">Bounding Box (bbox)</label>
                        <input type="text" id="frag-bbox" class="form-control form-control-sm" placeholder="x1,y1,x2,y2">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="frag-text">Đoạn văn bản trích dẫn</label>
                    <textarea id="frag-text" class="form-control form-control-sm" rows="3" placeholder="Nhập trích đoạn nội dung hoặc ghi chú lát cắt..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="frag-tag">Gán tag cụ thể cho Fragment</label>
                    <input type="text" id="frag-tag" class="form-control form-control-sm" placeholder="Ví dụ: machine-learning/cnn">
                </div>
                <button type="submit" class="btn btn-sm btn-primary btn-block">Tạo Lát cắt Fragment</button>
            </form>
        </div>
    </div>
</div>
