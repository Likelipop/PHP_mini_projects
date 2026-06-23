<!-- CodeMirror 5 CDN dependencies for Markdown styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/nord.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/markdown/markdown.min.js"></script>

<div class="row g-3 notes-layout" x-data="noteEditorData()" @select-note-by-id.window="const found = notes.find(n => n.id == $event.detail.id); if(found) selectNote(found);">
    <!-- Left panel: Notes List -->
    <div class="col-md-3 border-end pe-3" style="max-height: calc(100vh - 220px); overflow-y: auto;">
        <div class="d-grid mb-3">
            <button class="btn btn-sm btn-primary" @click="createNewNote()">
                <i class="fa-solid fa-plus me-1"></i> Ghi chú mới
            </button>
        </div>
        
        <div class="list-group list-group-flush border rounded shadow-sm">
            <template x-for="n in notes" :key="n.id">
                <button class="list-group-item list-group-item-action py-2.5 text-start note-list-card"
                        :class="activeNote && activeNote.id === n.id ? 'active' : ''"
                        :data-id="n.id"
                        @click="selectNote(n)">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold xsmall text-truncate" style="max-width: 140px;" x-text="n.title"></span>
                        <span class="xsmall text-muted font-monospace" style="font-size: 0.7rem;" x-text="formatDate(n.created_at)"></span>
                    </div>
                    <p class="mb-0 text-muted xsmall text-truncate" x-text="n.content || 'Ghi chú trống...'"></p>
                </button>
            </template>
        </div>
    </div>

    <!-- Right panel: Workspace Editor (CodeMirror & Markdown render) -->
    <div class="col-md-9 d-flex flex-column" style="min-height: 480px; max-height: calc(100vh - 220px);" x-show="activeNote">
        <!-- Editor Header Title -->
        <div class="card shadow-sm border border-secondary-subtle mb-2">
            <div class="card-body p-2 d-flex gap-3 align-items-center">
                <div class="flex-grow-1">
                    <input type="text" class="form-control form-control-sm border-0 fw-bold bg-transparent px-0" 
                           placeholder="Tiêu đề ghi chú..." x-model="activeNoteTitle" style="font-size: 1.1rem; box-shadow: none;">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" @click="saveNote()"><i class="fa-regular fa-floppy-disk me-1"></i> Lưu</button>
                </div>
            </div>
        </div>

        <!-- Toolbar and view modes -->
        <div class="d-flex justify-content-between align-items-center border border-bottom-0 rounded-top p-2 bg-body-secondary small">
            <!-- Toolbar buttons -->
            <div class="d-flex gap-1">
                <button class="btn btn-xs btn-outline-secondary" @click="insertText('**', '**')" title="Bold"><i class="fa-solid fa-bold"></i></button>
                <button class="btn btn-xs btn-outline-secondary" @click="insertText('*', '*')" title="Italic"><i class="fa-solid fa-italic"></i></button>
                <button class="btn btn-xs btn-outline-secondary" @click="insertText('### ', '')" title="Header"><i class="fa-solid fa-heading"></i></button>
                <button class="btn btn-xs btn-outline-secondary" @click="insertText('`', '`')" title="Code inline"><i class="fa-solid fa-code"></i></button>
                <button class="btn btn-xs btn-outline-secondary" @click="insertText('@', '')" title="Nhúng tài nguyên"><i class="fa-solid fa-link"></i></button>
            </div>
            
            <!-- View toggles (Obsidian Write vs Split View) -->
            <div class="d-flex gap-1 border-start ps-2">
                <button class="btn btn-xs btn-outline-secondary" :class="viewMode === 'write' ? 'active' : ''" @click="setViewMode('write')">Viết (Write)</button>
                <button class="btn btn-xs btn-outline-secondary" :class="viewMode === 'split' ? 'active' : ''" @click="setViewMode('split')">Song song (Split)</button>
                <button class="btn btn-xs btn-outline-secondary" :class="viewMode === 'preview' ? 'active' : ''" @click="setViewMode('preview')">Xem (Preview)</button>
            </div>
        </div>

        <!-- Editor body area -->
        <div class="flex-grow-1 row g-0 border rounded-bottom overflow-hidden bg-body" style="height: 380px;">
            <!-- CodeMirror Editor container -->
            <div class="col-md-6 h-100 border-end" x-show="viewMode === 'write' || viewMode === 'split'">
                <textarea id="note-cm-textarea" class="w-100 h-100 d-none"></textarea>
            </div>
            
            <!-- Markdown Render preview container -->
            <div class="col h-100 p-3 overflow-auto markdown-rendered" id="note-markdown-preview-pane" x-show="viewMode === 'preview' || viewMode === 'split'">
                <!-- Rendered text output -->
            </div>
        </div>
    </div>

    <!-- Empty state panel -->
    <div class="col-md-9 text-center m-auto text-muted small py-5" x-show="!activeNote">
        <i class="fa-solid fa-note-sticky fs-1 mb-2"></i>
        <p class="mb-0">Chọn một ghi chú bên trái hoặc click tạo mới để bắt đầu soạn thảo</p>
    </div>
</div>

<script>
    function noteEditorData() {
        return {
            notes: [],
            activeNote: null,
            activeNoteTitle: '',
            viewMode: 'split', // write, split, preview
            cmEditor: null,
            
            init() {
                this.notes = <?= json_encode($notes) ?>;
            },
            
            formatDate(str) {
                if (!str) return 'Mới';
                const d = new Date(str);
                return d.toLocaleDateString('vi-VN');
            },
            
            createNewNote() {
                const newN = {
                    id: 'temp_' + Date.now(),
                    title: 'Ghi chú mới',
                    content: '',
                    created_at: new Date().toISOString()
                };
                this.notes.unshift(newN);
                this.selectNote(newN);
            },
            
            selectNote(note) {
                this.activeNote = note;
                this.activeNoteTitle = note.title;
                
                // Initialize CodeMirror after DOM binds
                this.$nextTick(() => {
                    const txt = document.getElementById('note-cm-textarea');
                    if (this.cmEditor) {
                        this.cmEditor.setValue(note.content || '');
                    } else {
                        this.cmEditor = CodeMirror.fromTextArea(txt, {
                            mode: 'markdown',
                            theme: 'nord',
                            lineNumbers: true,
                            lineWrapping: true
                        });
                        
                        // Render preview on changes
                        this.cmEditor.on('change', () => {
                            this.renderMarkdown();
                        });
                    }
                    this.cmEditor.refresh();
                    this.renderMarkdown();
                });
            },
            
            setViewMode(mode) {
                this.viewMode = mode;
                this.$nextTick(() => {
                    if (this.cmEditor) this.cmEditor.refresh();
                    this.renderMarkdown();
                });
            },
            
            insertText(before, after) {
                if (!this.cmEditor) return;
                const doc = this.cmEditor.getDoc();
                const cursor = doc.getCursor();
                const selection = doc.getSelection();
                doc.replaceSelection(before + selection + after);
                this.cmEditor.focus();
            },
            
            renderMarkdown() {
                if (!this.cmEditor) return;
                const previewPane = document.getElementById('note-markdown-preview-pane');
                if (!previewPane) return;
                
                const mdContent = this.cmEditor.getValue();
                
                // 1. Initialize Markdown-It parser
                const md = window.markdownit({
                    html: true,
                    linkify: true,
                    typographer: true
                });
                
                let renderedHtml = md.render(mdContent);
                
                // 2. Custom Resource Embedding parsing (@resource_key)
                // Convert @filename to image tags or links using S3 resources database
                const resourcesList = window.resourcesList || [];
                renderedHtml = renderedHtml.replace(/@([a-zA-Z0-9_\-\.\u00C0-\u1EF9]+?)(?:_page|_p|#page=)?([0-9]+)?\b/g, (match, key, page) => {
                    const res = resourcesList.find(r => r.title.toLowerCase().includes(key.toLowerCase()) || r.id == key);
                    if (res) {
                        const pageNum = page ? parseInt(page) : 1;
                        if (res.file_type === 'image') {
                            return `<div class="embedded-resource p-2 border rounded my-2 text-center bg-body-tertiary">
                                <img src="${res.presigned_url}" class="img-fluid rounded border mb-1" style="max-height: 200px;">
                                <div class="xsmall text-muted font-monospace"><i class="fa-regular fa-image"></i> ${res.title}</div>
                            </div>`;
                        } else if (res.file_type === 'pdf') {
                            return `<div class="embedded-resource p-2 border rounded my-2 bg-body-tertiary d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                    <span class="small fw-bold">${res.title} (Trang ${pageNum})</span>
                                </div>
                                <a href="${res.presigned_url}#page=${pageNum}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-eye"></i> Xem file</a>
                            </div>`;
                        } else {
                            return `<div class="embedded-resource p-2 border rounded my-2 bg-body-tertiary d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa-solid fa-file-lines text-info me-2"></i>
                                    <span class="small fw-bold">${res.title}</span>
                                </div>
                                <a href="${res.presigned_url}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-eye"></i> Xem file</a>
                            </div>`;
                        }
                    }
                    return `<span class="badge bg-secondary-subtle text-secondary border font-monospace">@${key}${page ? '_p' + page : ''}</span>`;
                });
                
                previewPane.innerHTML = renderedHtml;
                
                // 3. Render Mermaid diagrams dynamically
                this.$nextTick(() => {
                    const mermaidBlocks = previewPane.querySelectorAll('pre code.language-mermaid');
                    mermaidBlocks.forEach((block, index) => {
                        const rawCode = block.textContent;
                        const parent = block.parentElement;
                        const graphDiv = document.createElement('div');
                        graphDiv.className = 'mermaid-rendered-graph my-3';
                        graphDiv.id = 'mermaid-graph-' + index;
                        graphDiv.innerHTML = rawCode;
                        parent.replaceWith(graphDiv);
                    });
                    try {
                        window.mermaid.init(undefined, '.mermaid-rendered-graph');
                    } catch (err) {
                        console.error('Mermaid render error', err);
                    }
                });
            },
            
            async saveNote() {
                if (!this.cmEditor) return;
                const content = this.cmEditor.getValue();
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= csrf_token() ?>');
                formData.append('studyflow_id', '<?= $flow['id'] ?>');
                formData.append('title', this.activeNoteTitle);
                formData.append('content', content);
                
                // If it is a newly created note, URL should create, else edit
                const isNew = String(this.activeNote.id).startsWith('temp_');
                const url = isNew ? '/note/create' : `/note/${this.activeNote.id}/save`;
                
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (isNew) {
                            this.activeNote.id = data.note.id;
                            this.activeNote.created_at = data.note.created_at;
                        }
                        this.activeNote.title = this.activeNoteTitle;
                        this.activeNote.content = content;
                        
                        if (window.showToast) window.showToast('Lưu ghi chú thành công!', 'success');
                    } else {
                        alert(data.error || 'Có lỗi xảy ra');
                    }
                } catch (err) {
                    console.error('Save note error', err);
                }
            }
        };
    }
</script>
