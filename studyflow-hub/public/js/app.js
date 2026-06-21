document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Workspace Tabs Navigation
    // ----------------------------------------------------
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            
            // Toggle buttons
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Toggle panes
            tabPanes.forEach(pane => {
                if (pane.id === tabId) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });
        });
    });

    // ----------------------------------------------------
    // 2. TagBench Autocomplete & Search Filter
    // ----------------------------------------------------
    const tagFilterInput = document.getElementById('tagbench-filter');
    const tagAutocomplete = document.getElementById('tagbench-autocomplete');
    const tagLinks = document.querySelectorAll('.tag-tree-link');

    if (tagFilterInput) {
        tagFilterInput.addEventListener('input', async (e) => {
            const val = e.target.value.trim();
            if (val.length < 1) {
                tagAutocomplete.style.display = 'none';
                filterTreeNodes('');
                return;
            }

            // Client side node filtering
            filterTreeNodes(val);

            // Fetch autocomplete from API
            try {
                const response = await fetch(`/api/tags/search?q=${encodeURIComponent(val)}`);
                const tags = await response.json();
                
                if (tags.length > 0) {
                    tagAutocomplete.innerHTML = '';
                    tags.forEach(tag => {
                        const li = document.createElement('li');
                        li.className = 'tagbench-autocomplete-item';
                        li.textContent = tag.prefix;
                        li.addEventListener('click', () => {
                            tagFilterInput.value = tag.prefix;
                            tagAutocomplete.style.display = 'none';
                            // Redirect to filter by tag
                            const slug = document.querySelector('.workspace-wrapper').getAttribute('data-flow-slug');
                            window.location.href = `/studyflow/${slug}?tag=${encodeURIComponent(tag.prefix)}`;
                        });
                        tagAutocomplete.appendChild(li);
                    });
                    tagAutocomplete.style.display = 'block';
                } else {
                    tagAutocomplete.style.display = 'none';
                }
            } catch (err) {
                console.error('Error fetching tags', err);
            }
        });

        // Hide autocomplete on click outside
        document.addEventListener('click', (e) => {
            if (!tagFilterInput.contains(e.target) && !tagAutocomplete.contains(e.target)) {
                tagAutocomplete.style.display = 'none';
            }
        });
    }

    function filterTreeNodes(query) {
        const queryLower = query.toLowerCase();
        tagLinks.forEach(link => {
            const text = link.textContent.toLowerCase();
            const parentLi = link.closest('.tag-tree-item');
            
            if (parentLi) {
                if (text.includes(queryLower)) {
                    parentLi.style.display = '';
                    // Ensure parents are visible too
                    let ancestor = parentLi.parentElement.closest('.tag-tree-item');
                    while (ancestor) {
                        ancestor.style.display = '';
                        ancestor = ancestor.parentElement.closest('.tag-tree-item');
                    }
                } else {
                    parentLi.style.display = 'none';
                }
            }
        });
    }

    // Collapsible Tag Tree toggle
    const toggles = document.querySelectorAll('.tag-tree-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            const item = toggle.closest('.tag-tree-item');
            const subList = item.querySelector('.tag-tree-list');
            if (subList) {
                if (subList.style.display === 'none') {
                    subList.style.display = 'block';
                    toggle.innerHTML = '<i class="fa-solid fa-caret-down"></i>';
                } else {
                    subList.style.display = 'none';
                    toggle.innerHTML = '<i class="fa-solid fa-caret-right"></i>';
                }
            }
            e.preventDefault();
            e.stopPropagation();
        });
    });

    // ----------------------------------------------------
    // 3. Notes Sidebar actions & Editor
    // ----------------------------------------------------
    const btnCreateNoteTrigger = document.getElementById('btn-create-note-trigger');
    const notesEditorPanel = document.getElementById('notes-editor-panel');
    const noteForm = document.getElementById('note-form');
    const noteIdInput = document.getElementById('note-id-input');
    const noteTitle = document.getElementById('note-title');
    const noteMarkdown = document.getElementById('note-markdown');
    const noteTags = document.getElementById('note-tags');
    const noteCards = document.querySelectorAll('.note-list-card');

    if (btnCreateNoteTrigger) {
        btnCreateNoteTrigger.addEventListener('click', () => {
            // Reset form for create
            noteForm.action = `/studyflow/${getFlowSlug()}/notes/create`;
            noteIdInput.value = '';
            noteTitle.value = '';
            noteMarkdown.value = '';
            noteTags.value = '';
            notesEditorPanel.style.display = 'block';
            noteCards.forEach(c => c.classList.remove('active'));
            switchToWriteMode();
        });
    }

    noteCards.forEach(card => {
        // Edit button click inside note card
        const editBtn = card.querySelector('.btn-note-edit-action');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                
                const id = card.getAttribute('data-id');
                const title = card.getAttribute('data-title');
                const markdown = card.getAttribute('data-markdown');
                const tags = card.getAttribute('data-tags');

                // Load note to editor
                noteForm.action = `/studyflow/${getFlowSlug()}/notes/${id}/edit`;
                noteIdInput.value = id;
                noteTitle.value = title;
                noteMarkdown.value = markdown;
                noteTags.value = tags;
                
                notesEditorPanel.style.display = 'block';
                
                noteCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                
                switchToWriteMode();
            });
        }
        
        // Clicking card itself loads or edits
        card.addEventListener('click', () => {
            const editBtn = card.querySelector('.btn-note-edit-action');
            if (editBtn) editBtn.click();
        });
    });

    function getFlowSlug() {
        return document.querySelector('.workspace-wrapper').getAttribute('data-flow-slug');
    }

    // ----------------------------------------------------
    // 4. Minimal Markdown Write / Live Preview Toggle & Transclusion
    // ----------------------------------------------------
    const editorWriteBtn = document.getElementById('editor-write-btn');
    const editorPreviewBtn = document.getElementById('editor-preview-btn');
    const editorTextareaSide = document.getElementById('editor-textarea-side');
    const editorPreviewSide = document.getElementById('editor-preview-side');
    const notePreviewContent = document.getElementById('note-preview-content');

    if (editorWriteBtn && editorPreviewBtn) {
        editorWriteBtn.addEventListener('click', switchToWriteMode);
        editorPreviewBtn.addEventListener('click', switchToPreviewMode);
    }

    function switchToWriteMode() {
        editorWriteBtn.classList.add('active');
        editorPreviewBtn.classList.remove('active');
        editorTextareaSide.style.display = 'block';
        editorPreviewSide.style.display = 'none';
    }

    function switchToPreviewMode() {
        editorWriteBtn.classList.remove('active');
        editorPreviewBtn.classList.add('active');
        editorTextareaSide.style.display = 'none';
        editorPreviewSide.style.display = 'block';
        
        renderMarkdownPreview();
    }

    function renderMarkdownPreview() {
        let rawText = noteMarkdown.value;

        // 1. Process Transclusions: replaces @resource_filename with embedded links or tags
        rawText = parseTransclusions(rawText);

        // 2. Simple markdown parser rules (Fallback client rendering)
        let html = rawText
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/_(.*?)_/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/^\s*# (.*?)$/gm, '<h1>$1</h1>')
            .replace(/^\s*## (.*?)$/gm, '<h2>$1</h2>')
            .replace(/^\s*### (.*?)$/gm, '<h3>$1</h3>')
            .replace(/\n/g, '<br>');

        notePreviewContent.innerHTML = html;
    }

    function parseTransclusions(text) {
        // Find all @[a-zA-Z0-9_.-]+ symbols representing resource links
        const transclusionRegex = /@([a-zA-Z0-9_\.\-]+)/g;
        
        // Find all resource card elements to map filenames/titles to download links
        const resources = Array.from(document.querySelectorAll('.resource-card'));
        const notes = Array.from(document.querySelectorAll('.note-list-card'));

        return text.replace(transclusionRegex, (match, resourceName) => {
            // Find resource card by name/title matching key
            const resCard = resources.find(card => {
                const title = card.getAttribute('data-title');
                // Strip extension to match e.g. @lecture3
                const titleWithoutExt = title.substring(0, title.lastIndexOf('.')) || title;
                return title.toLowerCase() === resourceName.toLowerCase() ||
                       titleWithoutExt.toLowerCase() === resourceName.toLowerCase();
            });

            if (resCard) {
                const id = resCard.getAttribute('data-id');
                const title = resCard.getAttribute('data-title');
                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(title);

                if (isImage) {
                    return `<img src="/assets/download/${id}" class="note-editor-transclusion-img" alt="${title}">`;
                } else {
                    return `<div class="note-editor-transclusion-snippet"><i class="fa-solid fa-file-pdf"></i> Tài liệu: <a href="/assets/download/${id}" target="_blank">${title}</a></div>`;
                }
            }

            // Find note by title
            const noteCard = notes.find(card => card.getAttribute('data-title').toLowerCase() === resourceName.toLowerCase());
            if (noteCard) {
                const title = noteCard.getAttribute('data-title');
                const md = noteCard.getAttribute('data-markdown');
                return `<div class="note-editor-transclusion-snippet"><i class="fa-solid fa-note-sticky"></i> Transclusion: <strong>${title}</strong><div class="transcluded-note-body">${md}</div></div>`;
            }

            return match; // return original if not found
        });
    }

    // ----------------------------------------------------
    // 5. Toolbar button triggers
    // ----------------------------------------------------
    const toolButtons = document.querySelectorAll('.tool-btn');
    toolButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const syntax = btn.getAttribute('data-syntax');
            if (syntax) {
                insertSyntaxAtCursor(noteMarkdown, syntax);
            }
        });
    });

    const toolEmbedResource = document.getElementById('tool-embed-resource');
    if (toolEmbedResource) {
        toolEmbedResource.addEventListener('click', () => {
            const firstResource = document.querySelector('.resource-card');
            if (firstResource) {
                const title = firstResource.getAttribute('data-title');
                const nameWithoutExt = title.substring(0, title.lastIndexOf('.')) || title;
                insertSyntaxAtCursor(noteMarkdown, `@${nameWithoutExt}`);
            } else {
                insertSyntaxAtCursor(noteMarkdown, '@resource_name');
            }
        });
    }

    const toolInsertTag = document.getElementById('tool-insert-tag');
    if (toolInsertTag) {
        toolInsertTag.addEventListener('click', () => {
            insertSyntaxAtCursor(noteMarkdown, '#tag_name');
        });
    }

    function insertSyntaxAtCursor(textarea, syntax) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selected = text.substring(start, end);
        
        let replacement = '';
        if (syntax === '**' || syntax === '_' || syntax === '`') {
            replacement = syntax + selected + syntax;
        } else if (syntax === '#') {
            replacement = '\n# ' + selected;
        } else {
            replacement = syntax;
        }

        textarea.value = text.substring(0, start) + replacement + text.substring(end);
        textarea.focus();
        textarea.selectionStart = start + replacement.length;
        textarea.selectionEnd = start + replacement.length;
    }

    // ----------------------------------------------------
    // 6. Drag and Drop upload dropzone
    // ----------------------------------------------------
    const dropzone = document.getElementById('resources-dropzone');
    const uploadForm = document.querySelector('.upload-inline-form');
    const fileInput = uploadForm ? uploadForm.querySelector('input[type="file"]') : null;

    if (dropzone && fileInput && uploadForm) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                // Auto submit form
                uploadForm.submit();
            }
        });

        // Dropzone click opens file dialog
        dropzone.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadForm.submit();
            }
        });
    }

    // ----------------------------------------------------
    // 7. Granular Asset Fragments creation modal
    // ----------------------------------------------------
    const fragmentModal = document.getElementById('fragment-modal');
    const fragmentModalClose = document.getElementById('fragment-modal-close');
    const fragmentForm = document.getElementById('fragment-form');
    const fragAssetId = document.getElementById('frag-asset-id');
    const fragAssetTitle = document.getElementById('frag-asset-title');

    document.querySelectorAll('.btn-fragment-maker').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.resource-card');
            const id = card.getAttribute('data-id');
            const title = card.getAttribute('data-title');

            fragAssetId.value = id;
            fragAssetTitle.value = title;
            fragmentModal.style.display = 'flex';
        });
    });

    if (fragmentModalClose) {
        fragmentModalClose.addEventListener('click', () => {
            fragmentModal.style.display = 'none';
        });
    }

    if (fragmentForm) {
        fragmentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const payload = {
                asset_id: fragAssetId.value,
                tag_name: document.getElementById('frag-tag').value,
                page: document.getElementById('frag-page').value,
                bbox: document.getElementById('frag-bbox').value,
                text: document.getElementById('frag-text').value,
            };

            const formData = new FormData();
            for (const key in payload) {
                formData.append(key, payload[key]);
            }

            try {
                const response = await fetch('/api/fragments/create', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Tạo lát cắt tri thức (Fragment) thành công!');
                    fragmentModal.style.display = 'none';
                    fragmentForm.reset();
                } else {
                    alert('Lỗi tạo fragment: ' + result.error);
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi xảy ra.');
            }
        });
    }
});
