<style>
/* Custom minimal scrollbar */
.minimal-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
.minimal-scroll::-webkit-scrollbar-track { background: transparent; }
.minimal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.minimal-scroll:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
.file-tree-item { cursor: pointer; user-select: none; padding: 4px 8px; border-radius: 4px; margin-bottom: 2px; }
.file-tree-item:hover { background: var(--bs-secondary-bg); }
.file-tree-item.active { background: var(--bs-primary-bg-subtle); font-weight: 600; color: var(--bs-primary); }
.tree-line { border-left: 1px solid var(--bs-border-color); margin-left: 0.5rem; padding-left: 0.5rem; }
</style>

<div class="workspace-container d-flex flex-column h-100" style="height: calc(100vh - 60px) !important;" 
     x-data="studyflowWorkspace()" 
     data-flow-id="<?= $flow['id'] ?>"
     x-init="initWorkspace()">
     
    <!-- Header -->
    <header class="border-bottom px-3 py-2 bg-body-tertiary d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 font-monospace small">
            <a href="/studyflows" class="text-decoration-none text-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Hub</a>
            <span class="text-secondary">/</span>
            <span class="fw-bold text-body"><?= h($flow['title']) ?></span>
            <span class="badge bg-secondary-subtle text-secondary border xsmall ms-2">@<?= h($flow['username'] ?? 'owner') ?></span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="alert('Share link copied!')"><i class="fa-solid fa-share-nodes"></i></button>
            <?php if ((int)$flow['user_id'] === (int)$_SESSION['user_id']): ?>
                <form action="/studyflow/<?= h($flow['slug']) ?>/delete" method="POST" class="d-inline mb-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa StudyFlow này?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fa-solid fa-trash-can"></i></button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <div class="row g-0 flex-grow-1 overflow-hidden">
        <!-- Column A: ResourceHub (col-2) -->
        <div class="col-2 border-end d-flex flex-column bg-body-tertiary h-100">
            <!-- Toolbar -->
            <div class="p-2 border-bottom d-flex gap-1 justify-content-between align-items-center bg-body shadow-sm" style="z-index: 10;">
                <span class="fw-bold text-secondary xsmall text-uppercase ps-1">Resources</span>
                <div class="d-flex gap-1">
                    <div class="dropdown">
                        <button class="btn btn-xs btn-outline-secondary px-2" data-bs-toggle="dropdown" title="Quản lý Tag" :disabled="!activeAsset">
                            <i class="fa-solid fa-tags"></i>
                        </button>
                        <div class="dropdown-menu p-2 shadow-sm border-secondary-subtle" style="width: 220px; font-size: 0.85rem;">
                            <h6 class="dropdown-header px-1 py-1 text-primary"><i class="fa-solid fa-tag me-1"></i> Tags</h6>
                            <div class="d-flex flex-wrap gap-1 mb-2" x-show="activeAsset && activeAssetTags.length > 0">
                                <template x-for="tag in activeAssetTags" :key="tag">
                                    <span class="badge bg-secondary-subtle text-body border d-flex align-items-center gap-1 font-monospace">
                                        <span x-text="tag"></span>
                                        <i class="fa-solid fa-xmark text-danger" style="cursor:pointer" @click.stop="removeTagFromActiveAsset(tag)"></i>
                                    </span>
                                </template>
                            </div>
                            <div class="text-secondary xsmall px-1 mb-2 fst-italic" x-show="!activeAsset || activeAssetTags.length === 0">Chưa có tag.</div>
                            <input type="text" class="form-control form-control-sm font-monospace" placeholder="+ Add tag (Enter)" 
                                   x-model="newTagInput" @keydown.enter.prevent="addTagToActiveAsset()">
                        </div>
                    </div>
                    <button class="btn btn-xs btn-outline-secondary px-2" @click="openMakeFolderModal()" title="Tạo thư mục (A2)">
                        <i class="fa-solid fa-folder-plus"></i>
                    </button>
                    <button class="btn btn-xs btn-primary px-2" @click="openUploadModal()" title="Upload (A1)">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </button>
                </div>
            </div>
            
            <!-- File Tree -->
            <div class="flex-grow-1 overflow-auto minimal-scroll p-2 font-monospace" style="font-size: 0.85rem;">
                <!-- Notes Folder -->
                <div class="mb-2">
                    <div class="file-tree-item fw-bold text-body d-flex align-items-center" @click="toggleFolder('notes')" @contextmenu.prevent="rightClickFolder('notes')">
                        <i class="fa-solid fa-chevron-right text-secondary me-2" style="font-size: 0.7em; transition: transform 0.2s" :style="expandedFolders['notes'] ? 'transform: rotate(90deg)' : ''"></i>
                        <i class="fa-solid fa-book-journal-whills text-warning me-2"></i> notes/
                    </div>
                    <div class="tree-line mt-1" x-show="expandedFolders['notes']" x-collapse>
                        <template x-for="note in filteredNotes" :key="note.id">
                            <div class="file-tree-item text-truncate d-flex align-items-center" :class="activeAsset && activeAsset.id === note.id ? 'active' : ''"
                                 @click="openNote(note)" @contextmenu.prevent="rightClickAsset(note)" :title="note.title">
                                <i class="fa-brands fa-markdown text-info me-2"></i> <span x-text="note.title"></span>
                            </div>
                        </template>
                        <div class="file-tree-item text-secondary fst-italic" x-show="filteredNotes.length === 0">Trống</div>
                    </div>
                </div>

                <!-- Custom Folders -->
                <template x-for="folderName in Object.keys(folders)" :key="folderName">
                    <div class="mb-2" x-show="folderName !== 'Root' || folders['Root'].length > 0">
                        <div class="file-tree-item fw-bold text-body d-flex align-items-center" x-show="folderName !== 'Root'" 
                             @click="toggleFolder(folderName)" @contextmenu.prevent="rightClickFolder(folderName)"
                             :class="activeFolder === folderName ? 'bg-body border border-secondary-subtle' : ''">
                            <i class="fa-solid fa-chevron-right text-secondary me-2" style="font-size: 0.7em; transition: transform 0.2s" :style="expandedFolders[folderName] ? 'transform: rotate(90deg)' : ''"></i>
                            <i class="fa-solid fa-folder text-primary me-2"></i> <span x-text="folderName + '/'"></span>
                        </div>
                        
                        <div :class="folderName === 'Root' ? '' : 'tree-line mt-1'" x-show="folderName === 'Root' || expandedFolders[folderName]" x-collapse>
                            <div class="sortable-list" :data-folder="folderName">
                                <template x-for="res in filteredFolders[folderName] || []" :key="res.id">
                                    <div class="file-tree-item text-truncate d-flex align-items-center" :class="activeAsset && activeAsset.id === res.id ? 'active' : ''"
                                         @click="selectResource(res)" @contextmenu.prevent="rightClickAsset(res)" :title="res.title">
                                        <i class="fa-solid fa-file-pdf text-danger me-2" x-show="res.file_type === 'pdf'"></i>
                                        <i class="fa-solid fa-image text-success me-2" x-show="res.file_type === 'image'"></i>
                                        <i class="fa-solid fa-file text-secondary me-2" x-show="res.file_type === 'other'"></i>
                                        <span x-text="res.title"></span>
                                    </div>
                                </template>
                                <div class="file-tree-item text-secondary fst-italic" x-show="(filteredFolders[folderName] || []).length === 0 && folderName !== 'Root'">Trống</div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column B: TagBench (col-3) -->
        <div class="col-3 border-end d-flex flex-column bg-body h-100">
            <div class="p-2 border-bottom bg-body-tertiary">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-end-0"><i class="fa-solid fa-filter text-secondary xsmall"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0 shadow-none font-monospace xsmall bg-body text-body" placeholder="Lọc theo tag (B1)..." x-model="tagSearchQuery">
                    <button class="btn btn-outline-secondary border-start-0 border text-danger" x-show="activeTagFilter" @click="clearTagFilter()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            
            <div class="flex-grow-1 overflow-auto minimal-scroll p-2">
                <div class="list-group list-group-flush gap-1">
                    <template x-for="tag in filteredTagsList" :key="tag.id">
                        <button class="list-group-item list-group-item-action py-1.5 px-2 border rounded d-flex justify-content-between align-items-center text-body"
                                :class="activeTagFilter === tag.prefix ? 'bg-primary text-white border-primary shadow-sm' : 'border-secondary-subtle bg-body'"
                                @click="toggleTagFilter(tag.prefix)" style="transition: all 0.2s;">
                            <span class="text-truncate font-monospace small"><i class="fa-solid fa-hashtag text-secondary me-1"></i><span x-text="tag.prefix"></span></span>
                        </button>
                    </template>
                    <div class="text-secondary small text-center p-4 fst-italic" x-show="filteredTagsList.length === 0">Không có tag nào.</div>
                </div>
            </div>
        </div>

        <!-- Column C: NoteBench (col-7) -->
        <div class="col-7 d-flex flex-column bg-body h-100 position-relative">
            <!-- Toolbar -->
            <div class="p-2 border-bottom bg-body-tertiary justify-content-between align-items-center" :class="activeNote ? 'd-flex' : 'd-none'">
                <div class="d-flex gap-2 align-items-center">
                    <div class="btn-group btn-group-sm shadow-sm">
                        <button type="button" class="btn btn-light border-secondary-subtle"
                                :class="noteMode === 'edit' ? 'active text-primary fw-bold' : ''"
                                @click="noteMode = 'edit'"><i class="fa-solid fa-pen-nib me-1"></i> Edit</button>
                        <button type="button" class="btn btn-light border-secondary-subtle"
                                :class="noteMode === 'render' ? 'active text-primary fw-bold' : ''"
                                @click="noteMode = 'render'; renderMarkdown()"><i class="fa-solid fa-eye me-1"></i> Render</button>
                    </div>
                    
                    <div class="border-start border-2 ps-2 ms-1 d-flex gap-1" x-show="noteMode === 'edit'">
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle fw-bold text-body px-2" @click="insertFormat('**', '**')" title="In đậm">B</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle fst-italic text-body px-2" @click="insertFormat('*', '*')" title="In nghiêng">I</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle text-decoration-underline text-body px-2" @click="insertFormat('<u>', '</u>')" title="Gạch chân">U</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle text-body px-2" @click="insertFormat('- ', '')" title="Bullet point"><i class="fa-solid fa-list-ul"></i></button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle font-monospace text-body px-2" @click="insertFormat('`', '`')" title="Code">`</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle font-monospace text-body px-2" @click="insertFormat('# ', '')" title="H1">H1</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle font-monospace text-body px-2" @click="insertFormat('## ', '')" title="H2">H2</button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle text-body px-2" @click="insertFormat('[', '](url)')" title="Link"><i class="fa-solid fa-link"></i></button>
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle text-body px-2" @click="insertFormat('> ', '')" title="Quote"><i class="fa-solid fa-quote-right"></i></button>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-secondary-subtle d-flex align-items-center gap-1" data-bs-toggle="dropdown" title="Tags của Note này">
                            <i class="fa-solid fa-tags"></i> <span class="badge bg-primary rounded-pill xsmall" x-text="activeAssetTags.length" x-show="activeAssetTags.length > 0"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-2 shadow-sm border-secondary-subtle" style="width: 220px; font-size: 0.85rem;">
                            <h6 class="dropdown-header px-1 py-1 text-primary"><i class="fa-solid fa-tag me-1"></i> Tags của Note</h6>
                            <div class="d-flex flex-wrap gap-1 mb-2" x-show="activeAssetTags.length > 0">
                                <template x-for="tag in activeAssetTags" :key="tag">
                                    <span class="badge bg-secondary-subtle text-body border d-flex align-items-center gap-1 font-monospace">
                                        <span x-text="tag"></span>
                                        <i class="fa-solid fa-xmark text-danger" style="cursor:pointer" @click.stop="removeTagFromActiveAsset(tag)"></i>
                                    </span>
                                </template>
                            </div>
                            <div class="text-secondary xsmall px-1 mb-2 fst-italic" x-show="activeAssetTags.length === 0">Chưa có tag.</div>
                            <input type="text" class="form-control form-control-sm font-monospace" placeholder="+ Add tag (Enter)" 
                                   x-model="newTagInput" @keydown.enter.prevent="addTagToActiveAsset()">
                        </div>
                    </div>

                    <span class="xsmall text-secondary font-monospace d-flex align-items-center gap-1 border-start ps-2">
                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;" :class="saveStatus === 'Đang lưu...' ? 'text-warning' : 'text-success'"></i>
                        <span x-text="saveStatus"></span>
                    </span>
                </div>
            </div>

            <!-- Editor / Render Area -->
            <div class="flex-grow-1 position-relative h-100 bg-body" :class="activeNote ? '' : 'd-none'">
                <textarea id="note-editor-textarea" class="w-100 h-100 border-0 p-4 shadow-none minimal-scroll font-monospace bg-body text-body" 
                          style="resize: none; outline: none; font-size: 0.95rem; line-height: 1.6; position: absolute; top: 0; left: 0;" 
                          x-show="noteMode === 'edit'"
                          x-model="activeNoteContent"
                          @input="triggerAutoSave()"></textarea>
                <div class="h-100 w-100 p-4 overflow-auto minimal-scroll markdown-rendered text-body" 
                     style="position: absolute; top: 0; left: 0;"
                     x-show="noteMode === 'render'" x-html="renderedContent">
                </div>
            </div>

            <!-- Empty State -->
            <div class="h-100 w-100 align-items-center justify-content-center bg-body text-secondary flex-column position-absolute top-0 start-0" :class="!activeNote ? 'd-flex' : 'd-none'">
                <div class="text-center p-5 border border-dashed border-secondary-subtle rounded-4 bg-body-tertiary">
                    <i class="fa-solid fa-feather-pointed fs-1 mb-3 text-secondary"></i>
                    <h5 class="fw-bold text-body">NoteBench</h5>
                    <p class="small text-secondary mb-4">Click vào file note markdown để có thể chỉnh sửa.</p>
                    <button class="btn btn-primary shadow-sm" @click="createNoteModal()"><i class="fa-solid fa-plus me-2"></i> Tạo Note Mới</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Upload Modal A1 -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 bg-body-tertiary border-bottom-0">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Tải lên tài liệu</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="alert alert-primary py-2 xsmall mb-3 border-0 bg-primary-subtle d-flex align-items-center gap-2">
                        <i class="fa-solid fa-folder-open"></i> Đang tải lên thư mục: <strong class="font-monospace" x-text="uploadFolder"></strong>
                    </div>
                    <form @submit.prevent="submitUpload()">
                        <div class="mb-3">
                            <label class="form-label xsmall fw-bold text-secondary mb-1">Tên tài liệu</label>
                            <input type="text" class="form-control form-control-sm" x-model="uploadTitle" placeholder="Bỏ trống để lấy tên gốc của file">
                        </div>
                        <div class="mb-3">
                            <label class="form-label xsmall fw-bold text-secondary mb-1">Tags (phân cách bằng dấu phẩy)</label>
                            <input type="text" class="form-control form-control-sm font-monospace" x-model="uploadTags" placeholder="tag1, tag2">
                            <div class="form-text text-secondary" style="font-size: 0.7rem;">Mặc định sẽ lấy tên tài liệu làm tag nếu để trống.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label xsmall fw-bold text-secondary mb-1">Chọn file</label>
                            <input type="file" class="form-control form-control-sm" id="uploadFileInput" required>
                            <div class="form-text text-secondary" style="font-size: 0.7rem;">Tối đa 25MB mỗi file.</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3" :disabled="isUploading">
                                <span x-show="!isUploading">Upload</span>
                                <span x-show="isUploading"><span class="spinner-border spinner-border-sm me-2"></span>Đang tải...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal A2 -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content shadow border-0">
                <div class="modal-header py-2 bg-body-tertiary">
                    <h6 class="modal-title fw-bold xsmall"><i class="fa-solid fa-folder-plus text-primary me-2"></i>Tạo thư mục</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" style="font-size:0.6rem;"></button>
                </div>
                <div class="modal-body p-3">
                    <form @submit.prevent="submitCreateFolder()">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-sm font-monospace" x-model="newFolderName" placeholder="Tên thư mục..." required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Tạo mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Note Modal -->
    <div class="modal fade" id="createNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 bg-body-tertiary border-bottom-0">
                    <h6 class="modal-title fw-bold"><i class="fa-brands fa-markdown text-info me-2"></i>Tạo ghi chú mới</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <form @submit.prevent="submitCreateNote()">
                        <div class="mb-3">
                            <label class="form-label xsmall fw-bold text-secondary mb-1">Tiêu đề</label>
                            <input type="text" class="form-control form-control-sm" x-model="newNoteTitle" placeholder="Nhập tên ghi chú..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label xsmall fw-bold text-secondary mb-1">Tags (phân cách bằng dấu phẩy)</label>
                            <input type="text" class="form-control form-control-sm font-monospace" x-model="newNoteTags" placeholder="tag1, tag2">
                            <div class="form-text text-secondary" style="font-size: 0.7rem;">Mặc định sẽ gắn tag "untagged" nếu để trống.</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3" :disabled="isCreatingNote">
                                <span x-show="!isCreatingNote"><i class="fa-solid fa-plus me-1"></i> Tạo mới</span>
                                <span x-show="isCreatingNote"><span class="spinner-border spinner-border-sm me-2"></span>Đang tạo...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
function studyflowWorkspace() {
    return {
        flowId: <?= json_encode($flow['id']) ?>,
        notes: <?= json_encode($notes) ?>,
        resources: <?= json_encode($resources) ?>,
        folderNames: <?= json_encode($folderNames ?? []) ?>,
        tagsList: <?= json_encode($tags) ?>,
        
        folders: { 'Root': [] },
        expandedFolders: { 'notes': true, 'Root': true },
        activeFolder: null,
        activeAsset: null,
        activeAssetTags: [],
        
        tagSearchQuery: '',
        activeTagFilter: '',
        newTagInput: '',
        
        activeNote: null,
        activeNoteContent: '',
        noteMode: 'edit',
        renderedContent: '',
        saveTimeout: null,
        saveStatus: 'Đã lưu',
        mdParser: null,
        
        uploadFolder: 'Root',
        uploadTitle: '',
        uploadTags: '',
        isUploading: false,
        newFolderName: '',
        newNoteTitle: '',
        newNoteTags: '',
        isCreatingNote: false,
        
        get filteredNotes() {
            if (!this.activeTagFilter) return this.notes;
            return this.notes.filter(n => n.tags && n.tags.some(t => t.startsWith(this.activeTagFilter)));
        },
        
        get filteredFolders() {
            if (!this.activeTagFilter) return this.folders;
            let result = {};
            for (let folder in this.folders) {
                result[folder] = this.folders[folder].filter(r => r.tags && r.tags.some(t => t.startsWith(this.activeTagFilter)));
            }
            return result;
        },
        
        get filteredTagsList() {
            if (!this.tagSearchQuery) return this.tagsList;
            let q = this.tagSearchQuery.toLowerCase();
            return this.tagsList.filter(t => t.prefix.includes(q));
        },
        
        initWorkspace() {
            this.folders = { 'Root': [] };
            if (this.folderNames) {
                this.folderNames.forEach(fName => {
                    this.folders[fName] = [];
                });
            }
            this.resources.forEach(res => {
                let f = res.folder_name || 'Root';
                if (!this.folders[f]) this.folders[f] = [];
                this.folders[f].push(res);
            });
            for (let f in this.folders) {
                this.folders[f].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                this.expandedFolders[f] = true;
            }

            this.mdParser = window.markdownit({ html: true, linkify: true, breaks: true });
            
            this.$watch('noteMode', val => {
                if (val === 'render') this.renderMarkdown();
            });
            
            this.$nextTick(() => { this.initSortable(); });
        },
        
        initSortable() {
            document.querySelectorAll('.sortable-list').forEach(el => {
                new Sortable(el, {
                    group: 'shared',
                    animation: 150,
                    onEnd: (evt) => {
                        console.log('Moved item. Order update feature to be implemented securely via API.');
                    }
                });
            });
        },
        
        toggleFolder(name) {
            this.expandedFolders[name] = !this.expandedFolders[name];
            this.activeFolder = name;
        },
        
        selectResource(res) {
            this.activeAsset = res;
            this.activeAssetTags = res.tags || [];
            if (res.file_type === 'pdf' || res.file_type === 'image') {
                window.open(res.presigned_url, '_blank');
            } else {
                window.location.href = `/assets/download/${res.id}`;
            }
        },
        
        openNote(note) {
            this.activeAsset = note;
            this.activeAssetTags = note.tags || [];
            this.activeNote = note;
            this.activeNoteContent = note.content || note.markdown || '';
            this.noteMode = 'edit';
            this.saveStatus = 'Đã tải';
        },
        
        createNoteModal() {
            this.newNoteTitle = '';
            this.newNoteTags = '';
            new bootstrap.Modal(document.getElementById('createNoteModal')).show();
        },
        
        submitCreateNote() {
            if (!this.newNoteTitle.trim()) return;
            this.isCreatingNote = true;
            let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/note/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: csrf,
                    studyflow_id: this.flowId,
                    title: this.newNoteTitle.trim(),
                    content: '# ' + this.newNoteTitle.trim(),
                    tags: this.newNoteTags.trim() || 'untagged'
                })
            }).then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            }).then(data => {
                this.isCreatingNote = false;
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createNoteModal')).hide();
                    this.notes.unshift(data.note);
                    this.openNote(data.note);
                    if (window.showToast) window.showToast('Tạo ghi chú thành công!', 'success');
                } else {
                    alert(data.error || 'Có lỗi xảy ra');
                }
            }).catch(err => {
                this.isCreatingNote = false;
                console.error('Create note error', err);
                alert('Lỗi tạo ghi chú: ' + err.message);
            });
        },
        
        rightClickFolder(folderName) {
            if (folderName === 'notes' || folderName === 'Root') return;
            this.activeFolder = folderName;
            let action = prompt(`Thư mục: ${folderName}\n[1] Đổi tên\n[2] Tải file vào đây\nNhập 1 hoặc 2:`);
            if (action === '1') {
                let newName = prompt('Tên thư mục mới:', folderName);
                if (newName) alert('Tính năng Rename API đã sẵn sàng. Cần Reload để test.');
            } else if (action === '2') {
                this.openUploadModal(folderName);
            }
        },
        
        rightClickAsset(asset) {
            this.activeAsset = asset;
            let action = prompt(`Tài liệu: ${asset.title}\n[1] Đổi tên\n[2] Di chuyển\n[3] Xóa\nNhập số:`);
            if (action === '1') {
                let newTitle = prompt('Tên mới:', asset.title);
                if (newTitle) {
                    let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch(`/asset/${asset.id}/rename`, {
                        method: 'POST',
                        body: new URLSearchParams({ csrf_token: csrf, new_title: newTitle })
                    }).then(() => window.location.reload());
                }
            } else if (action === '2' && asset.type === 'resource') {
                let folder = prompt('Tên thư mục đích (Root hoặc tên folder):');
                if (folder) {
                    let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch(`/asset/${asset.id}/move`, {
                        method: 'POST',
                        body: new URLSearchParams({ csrf_token: csrf, folder_name: folder })
                    }).then(() => window.location.reload());
                }
            } else if (action === '3') {
                if (confirm('Xóa vĩnh viễn?')) {
                    fetch(`/asset/${asset.id}/delete`, {
                        method: 'POST',
                        body: new URLSearchParams({ csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') })
                    }).then(() => window.location.reload());
                }
            }
        },
        
        openUploadModal(folderName = null) {
            this.uploadFolder = folderName || this.activeFolder || 'Root';
            this.uploadTitle = '';
            this.uploadTags = '';
            document.getElementById('uploadFileInput').value = '';
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        },
        
        submitUpload() {
            let fileInput = document.getElementById('uploadFileInput');
            if (fileInput.files.length === 0) return;
            let file = fileInput.files[0];
            
            if (file.size > 26214400) {
                alert('File vượt quá giới hạn 25MB!');
                return;
            }
            
            this.isUploading = true;
            let formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
            formData.append('studyflow_id', this.flowId);
            formData.append('folder_name', this.uploadFolder);
            formData.append('title', this.uploadTitle);
            formData.append('tags', this.uploadTags);
            formData.append('file', file);
            
            fetch('/asset/upload', {
                method: 'POST',
                body: formData
            }).then(r => {
                if (!r.ok) throw new Error('Upload failed: ' + r.status);
                return r.json();
            }).then(d => {
                this.isUploading = false;
                if (d.success) {
                    bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                    if (!this.folders[this.uploadFolder]) this.folders[this.uploadFolder] = [];
                    this.folders[this.uploadFolder].unshift(d.asset);
                    if (window.showToast) window.showToast('Upload thành công!', 'success');
                } else {
                    alert(d.error || 'Có lỗi xảy ra khi upload.');
                }
            }).catch(err => {
                this.isUploading = false;
                console.error('Upload error', err);
                alert('Lỗi upload: ' + err.message);
            });
        },
        
        openMakeFolderModal() {
            this.newFolderName = '';
            new bootstrap.Modal(document.getElementById('createFolderModal')).show();
        },
        
        submitCreateFolder() {
            let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/api/folder/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: csrf,
                    studyflow_id: this.flowId,
                    title: this.newFolderName
                })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createFolderModal')).hide();
                    this.folders[this.newFolderName] = [];
                    this.expandedFolders[this.newFolderName] = true;
                }
            });
        },
        
        addTagToActiveAsset() {
            if (!this.newTagInput.trim() || !this.activeAsset) return;
            let tag = this.newTagInput.trim().toLowerCase();
            if (!this.activeAssetTags.includes(tag)) {
                this.activeAssetTags.push(tag);
                this.saveActiveAssetTags();
            }
            this.newTagInput = '';
        },
        
        removeTagFromActiveAsset(tag) {
            this.activeAssetTags = this.activeAssetTags.filter(t => t !== tag);
            this.saveActiveAssetTags();
        },
        
        saveActiveAssetTags() {
            if (!this.activeAsset) return;
            let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/api/assets/${this.activeAsset.id}/tags`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: csrf,
                    tags: this.activeAssetTags.join(',')
                })
            }).then(() => {
                this.activeAsset.tags = this.activeAssetTags;
            });
        },
        
        toggleTagFilter(tag) {
            if (this.activeTagFilter === tag) {
                this.clearTagFilter();
            } else {
                this.activeTagFilter = tag;
                this.activeNote = null; 
            }
        },
        
        clearTagFilter() {
            this.activeTagFilter = '';
            this.tagSearchQuery = '';
        },
        
        insertFormat(start, end) {
            let textarea = document.getElementById('note-editor-textarea');
            if (!textarea) return;
            let s = textarea.selectionStart;
            let e = textarea.selectionEnd;
            let val = textarea.value;
            let selected = val.substring(s, e);
            let before = val.substring(0, s);
            let after = val.substring(e);
            
            this.activeNoteContent = before + start + selected + end + after;
            this.triggerAutoSave();
            
            this.$nextTick(() => {
                textarea.focus();
                textarea.selectionStart = s + start.length;
                textarea.selectionEnd = s + start.length + selected.length;
            });
        },
        
        renderMarkdown() {
            if (!this.mdParser) return;
            let content = this.activeNoteContent;
            this.resources.forEach(res => {
                if (res.file_type === 'image') {
                    let regex = new RegExp(`!\\[.*?\\]\\(${res.title}\\)`, 'g');
                    content = content.replace(regex, `![img](${res.presigned_url})`);
                }
            });
            this.renderedContent = this.mdParser.render(content);
        },
        
        triggerAutoSave() {
            this.saveStatus = 'Đang lưu...';
            if (this.saveTimeout) clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveNote();
            }, 30000);
        },
        
        saveNote() {
            if (!this.activeNote) return;
            let csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/note/${this.activeNote.id}/save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: csrf,
                    title: this.activeNote.title,
                    content: this.activeNoteContent,
                    tags: (this.activeNote.tags || ['untagged']).join(',')
                })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    let date = new Date();
                    this.saveStatus = 'Đã lưu ' + date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
                    this.activeNote.content = this.activeNoteContent;
                } else {
                    this.saveStatus = 'Lỗi lưu';
                }
            });
        }
    };
}
</script>
