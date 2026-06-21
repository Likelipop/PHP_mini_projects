<div class="row g-3" x-data="resourceGridData()">
    <!-- Left side: folder lists and files grid -->
    <div class="col-md-7 d-flex flex-column gap-3">
        <!-- Search bar with HTMX -->
        <div class="card shadow-sm border border-secondary-subtle">
            <div class="card-body py-2 px-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Lọc tài nguyên trong StudyFlow..." 
                           x-model="searchQuery" @input="filterResources()">
                </div>
            </div>
        </div>

        <!-- Drag & Drop Upload Zone (S3 upload target) -->
        <div class="upload-zone p-3 border border-dashed rounded text-center cursor-pointer position-relative bg-body-tertiary"
             @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleFileDrop($event)"
             :class="dragOver ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle'">
            <input type="file" id="file-uploader-input" class="d-none" @change="handleFileSelect($event)" multiple>
            <label for="file-uploader-input" class="cursor-pointer mb-0">
                <i class="fa-solid fa-cloud-arrow-up text-primary fs-3 mb-2"></i>
                <h6 class="mb-1 fw-bold small">Kéo thả hoặc click để tải tài liệu lên MinIO/S3</h6>
                <p class="text-muted xsmall mb-0">Hỗ trợ PDF, PNG, JPG, ZIP (Tự động tạo tags theo thư mục)</p>
            </label>
            <!-- Progress bar -->
            <div class="progress progress-xs mt-2" x-show="uploadProgress > 0 && uploadProgress < 100">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" :style="'width: ' + uploadProgress + '%'"></div>
            </div>
        </div>

        <!-- Accordions Categories -->
        <div class="accordion border rounded shadow-sm" id="resourcesAccordion">
            <!-- Slides Category -->
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header" id="headingSlides">
                    <button class="accordion-button py-2.5 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSlides" aria-expanded="true" aria-controls="collapseSlides">
                        <i class="fa-solid fa-file-powerpoint text-primary me-2"></i> Bài giảng & Slides (Slides)
                    </button>
                </h2>
                <div id="collapseSlides" class="accordion-collapse collapse show" aria-labelledby="headingSlides" data-bs-parent="#resourcesAccordion">
                    <div class="accordion-body p-2">
                        <div class="row g-2 sortable-resource-grid" id="grid-slides" data-category="slides">
                            <template x-for="res in filteredSlides" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border border-secondary-subtle resource-card" @click="viewAsset(res)">
                                        <div class="text-center py-2">
                                            <i class="fa-solid fa-file-pdf text-danger fs-3"></i>
                                        </div>
                                        <div class="text-truncate fw-bold xsmall text-center text-body" x-text="res.title"></div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle xsmall text-muted">
                                            <span x-text="formatBytes(res.file_size)"></span>
                                            <div class="d-flex gap-1">
                                                <a :href="res.presigned_url" download class="text-muted" @click.stop><i class="fa-solid fa-download"></i></a>
                                                <?php if ((int)$flow['user_id'] === (int)$_SESSION['user_id']): ?>
                                                    <a href="#" class="text-danger" @click.prevent.stop="deleteAsset(res.id)"><i class="fa-solid fa-trash-can"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Category -->
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header" id="headingImages">
                    <button class="accordion-button collapsed py-2.5 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImages" aria-expanded="false" aria-controls="collapseImages">
                        <i class="fa-solid fa-file-image text-success me-2"></i> Minh họa & Sơ đồ (Images)
                    </button>
                </h2>
                <div id="collapseImages" class="accordion-collapse collapse" aria-labelledby="headingImages" data-bs-parent="#resourcesAccordion">
                    <div class="accordion-body p-2">
                        <div class="row g-2 sortable-resource-grid" id="grid-images" data-category="images">
                            <template x-for="res in filteredImages" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border border-secondary-subtle resource-card" @click="viewAsset(res)">
                                        <div class="text-center py-2 bg-light-subtle rounded mb-1 overflow-hidden" style="height: 60px;">
                                            <img :src="res.presigned_url" class="img-fluid h-100" style="object-fit: contain;">
                                        </div>
                                        <div class="text-truncate fw-bold xsmall text-center text-body" x-text="res.title"></div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle xsmall text-muted">
                                            <span x-text="formatBytes(res.file_size)"></span>
                                            <div class="d-flex gap-1">
                                                <a :href="res.presigned_url" download class="text-muted" @click.stop><i class="fa-solid fa-download"></i></a>
                                                <?php if ((int)$flow['user_id'] === (int)$_SESSION['user_id']): ?>
                                                    <a href="#" class="text-danger" @click.prevent.stop="deleteAsset(res.id)"><i class="fa-solid fa-trash-can"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Category -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingAssignments">
                    <button class="accordion-button collapsed py-2.5 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssignments" aria-expanded="false" aria-controls="collapseAssignments">
                        <i class="fa-solid fa-file-signature text-info me-2"></i> Bài tập & Tài liệu (Assignments)
                    </button>
                </h2>
                <div id="collapseAssignments" class="accordion-collapse collapse" aria-labelledby="headingAssignments" data-bs-parent="#resourcesAccordion">
                    <div class="accordion-body p-2">
                        <div class="row g-2 sortable-resource-grid" id="grid-assignments" data-category="assignments">
                            <template x-for="res in filteredAssignments" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border border-secondary-subtle resource-card" @click="viewAsset(res)">
                                        <div class="text-center py-2">
                                            <i class="fa-solid fa-file-lines text-info fs-3"></i>
                                        </div>
                                        <div class="text-truncate fw-bold xsmall text-center text-body" x-text="res.title"></div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle xsmall text-muted">
                                            <span x-text="formatBytes(res.file_size)"></span>
                                            <div class="d-flex gap-1">
                                                <a :href="res.presigned_url" download class="text-muted" @click.stop><i class="fa-solid fa-download"></i></a>
                                                <?php if ((int)$flow['user_id'] === (int)$_SESSION['user_id']): ?>
                                                    <a href="#" class="text-danger" @click.prevent.stop="deleteAsset(res.id)"><i class="fa-solid fa-trash-can"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side: Asset Viewer (PDF.js Canvas & Bounding Box highlight) -->
    <div class="col-md-5 d-flex flex-column border-start ps-3" style="max-height: calc(100vh - 200px);">
        <div class="card shadow-sm border border-secondary-subtle flex-grow-1 d-flex flex-column">
            <div class="card-header bg-body-tertiary fw-bold py-2.5 small d-flex justify-content-between align-items-center">
                <span><i class="fa-regular fa-eye text-primary me-2"></i> Trình xem tài liệu</span>
                <span class="badge bg-secondary-subtle text-secondary border font-monospace xsmall" x-text="viewingAsset ? viewingAsset.title : 'Chưa chọn file'"></span>
            </div>
            
            <div class="card-body p-2 d-flex flex-column justify-content-between overflow-hidden position-relative bg-dark-subtle" id="viewer-viewport" style="min-height: 400px;">
                <template x-if="!viewingAsset">
                    <div class="text-center m-auto text-muted small">
                        <i class="fa-regular fa-folder-open fs-2 mb-2"></i>
                        <p class="mb-0">Chọn một tài liệu bên trái để xem trước hoặc trích dẫn tag fragment</p>
                    </div>
                </template>

                <!-- PDF Viewer panel -->
                <div x-show="viewingAsset && viewingAsset.file_type === 'pdf'" class="w-100 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" x-transition>
                    <!-- PDF controls toolbar -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 bg-body-secondary p-2 rounded mb-2">
                        <div class="d-flex gap-1 align-items-center">
                            <button class="btn btn-xs btn-outline-secondary" @click="prevPdfPage()"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="xsmall font-monospace px-2 text-body"><span x-text="pdfPage">1</span>/<span x-text="pdfTotalPages">1</span></span>
                            <button class="btn btn-xs btn-outline-secondary" @click="nextPdfPage()"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            <button class="btn btn-xs btn-outline-secondary" @click="zoomPdf(0.9)"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                            <span class="xsmall font-monospace px-1" x-text="Math.round(pdfZoom * 100) + '%'">100%</span>
                            <button class="btn btn-xs btn-outline-secondary" @click="zoomPdf(1.1)"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                        </div>
                    </div>

                    <!-- PDF Canvas Area -->
                    <div class="flex-grow-1 overflow-auto d-flex justify-content-center align-items-start position-relative pdf-canvas-wrapper" id="pdf-scrollable-container" style="user-select: none;">
                        <canvas id="pdf-render-canvas" class="border shadow-xs cursor-crosshair" @mousedown="startFragmentSelection($event)" @mousemove="drawFragmentSelection($event)" @mouseup="endFragmentSelection($event)"></canvas>
                        <!-- Selector Box Overlay -->
                        <div class="position-absolute border border-primary border-2 bg-primary bg-opacity-25" id="pdf-crop-overlay" style="display: none; pointer-events: none;"></div>
                    </div>
                </div>

                <!-- Image previewer -->
                <div x-show="viewingAsset && viewingAsset.file_type === 'image'" class="w-100 h-100 d-flex align-items-center justify-content-center overflow-auto" x-transition>
                    <img :src="viewingAsset ? viewingAsset.presigned_url : ''" class="img-fluid border shadow-xs" style="max-height: 100%;">
                </div>
            </div>
        </div>
    </div>

    <!-- TippyJS Bounding Box Modal (Crop Fragment annotation form) -->
    <div id="crop-tippy-template" style="display: none;">
        <div class="p-3 shadow-lg border rounded text-body bg-body" style="width: 260px;">
            <div class="fw-bold small mb-2 border-bottom pb-1 text-primary"><i class="fa-solid fa-crop-simple me-1"></i> Tạo Tag Fragment</div>
            <div class="mb-2">
                <label class="xsmall fw-semibold text-muted mb-0.5">Trang & Box</label>
                <div class="form-control form-control-xs font-monospace bg-body-tertiary xsmall text-muted py-0.5" x-text="'P.' + pdfPage + ' ' + cropCoordsStr"></div>
            </div>
            <div class="mb-2">
                <label for="frag-tag" class="xsmall fw-semibold text-muted mb-0.5">Nhãn Tag <span class="text-danger">*</span></label>
                <input type="text" id="frag-tag" class="form-control form-control-xs" placeholder="ML/CNN..." x-model="cropTag">
            </div>
            <div class="mb-3">
                <label for="frag-note" class="xsmall fw-semibold text-muted mb-0.5">Ghi chú (Note)</label>
                <textarea id="frag-note" class="form-control form-control-xs" rows="2" placeholder="Nội dung note..." x-model="cropNote"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-1.5 pt-1.5 border-top">
                <button class="btn btn-xs btn-outline-secondary" @click="cancelCrop()">Hủy</button>
                <button class="btn btn-xs btn-primary fw-semibold" @click="saveCropFragment()"><i class="fa-regular fa-floppy-disk me-1"></i> Lưu</button>
            </div>
        </div>
    </div>
</div>

<script>
    function resourceGridData() {
        return {
            searchQuery: '',
            activeFilterTag: '',
            resources: [],
            dragOver: false,
            uploadProgress: 0,
            viewingAsset: null,
            
            // PDF parameters
            pdfDoc: null,
            pdfPage: 1,
            pdfTotalPages: 1,
            pdfZoom: 1.0,
            
            // Selection Crop coords
            isSelecting: false,
            startX: 0,
            startY: 0,
            cropCoords: null,
            cropCoordsStr: '',
            cropTag: '',
            cropNote: '',
            tippyInstance: null,

            init() {
                this.resources = <?= json_encode($resources) ?>;
                
                // Listen to TagBench tag selection events
                window.addEventListener('tag-selected', (e) => {
                    this.activeFilterTag = e.detail.tag;
                });
                
                // Initialize drag & drop reordering using SortableJS
                this.$nextTick(() => {
                    const grids = document.querySelectorAll('.sortable-resource-grid');
                    grids.forEach(grid => {
                        new Sortable(grid, {
                            animation: 150,
                            ghostClass: 'bg-primary-subtle',
                            onEnd: async (evt) => {
                                const order = Array.from(grid.querySelectorAll('.res-card-wrapper')).map(el => el.getAttribute('data-id'));
                                console.log('Reordered files list:', order);
                            }
                        });
                    });
                });
            },
            
            // Filters based on search text and selected tag bench tree node
            get filteredSlides() {
                return this.applyFilters(this.resources.filter(r => r.file_type === 'pdf'));
            },
            get filteredImages() {
                return this.applyFilters(this.resources.filter(r => r.file_type === 'image'));
            },
            get filteredAssignments() {
                return this.applyFilters(this.resources.filter(r => r.file_type !== 'pdf' && r.file_type !== 'image'));
            },
            
            applyFilters(list) {
                return list.filter(r => {
                    const matchesSearch = r.title.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchesTag = !this.activeFilterTag || 
                                       (this.activeFilterTag === 'untagged' && r.tags.length === 0) ||
                                       r.tags.some(t => t.startsWith(this.activeFilterTag));
                    return matchesSearch && matchesTag;
                });
            },
            
            formatBytes(bytes) {
                if (!bytes) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            },
            
            filterResources() {
                // Alpine handles rendering reactively
            },

            // File uploading using MinIO pre-signed URL bridge controller
            async handleFileDrop(e) {
                this.dragOver = false;
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    await this.uploadFiles(files);
                }
            },
            async handleFileSelect(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    await this.uploadFiles(files);
                }
            },
            async uploadFiles(files) {
                this.uploadProgress = 10;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('csrf_token', '<?= csrf_token() ?>');
                    formData.append('studyflow_id', '<?= $flow['id'] ?>');
                    formData.append('tags', this.activeFilterTag || 'untagged');
                    
                    try {
                        const res = await fetch('/asset/upload', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.resources.push(data.asset);
                            // Refresh layout
                            if (window.showToast) window.showToast('Tải lên thành công!', 'success');
                        } else {
                            if (window.showToast) window.showToast(data.error || 'Upload thất bại', 'danger');
                        }
                    } catch (err) {
                        console.error('Upload error', err);
                    }
                }
                this.uploadProgress = 100;
                setTimeout(() => this.uploadProgress = 0, 1500);
            },
            
            async deleteAsset(id) {
                if (!confirm('Bạn có chắc muốn xóa tài nguyên này?')) return;
                const formData = new FormData();
                formData.append('csrf_token', '<?= csrf_token() ?>');
                
                try {
                    const res = await fetch(`/asset/${id}/delete`, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.resources = this.resources.filter(r => r.id !== id);
                        if (this.viewingAsset && this.viewingAsset.id === id) this.viewingAsset = null;
                        if (window.showToast) window.showToast('Xóa tài nguyên thành công', 'success');
                    }
                } catch (err) {
                    console.error('Delete error', err);
                }
            },
            
            // View Asset logic (Dynamically triggers PDF.js renderer)
            viewAsset(asset) {
                this.viewingAsset = asset;
                this.cancelCrop();
                if (asset.file_type === 'pdf') {
                    this.pdfPage = 1;
                    this.pdfZoom = 1.0;
                    this.loadPdf(asset.presigned_url);
                }
            },
            
            async loadPdf(url) {
                try {
                    this.pdfDoc = await pdfjsLib.getDocument(url).promise;
                    this.pdfTotalPages = this.pdfDoc.numPages;
                    this.renderPdfPage();
                } catch (err) {
                    console.error('Error loading PDF:', err);
                }
            },
            
            async renderPdfPage() {
                if (!this.pdfDoc) return;
                try {
                    const page = await this.pdfDoc.getPage(this.pdfPage);
                    const canvas = document.getElementById('pdf-render-canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const viewport = page.getViewport({ scale: this.pdfZoom });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;
                } catch (err) {
                    console.error('Error rendering page:', err);
                }
            },
            
            prevPdfPage() {
                if (this.pdfPage > 1) {
                    this.pdfPage--;
                    this.renderPdfPage();
                    this.cancelCrop();
                }
            },
            nextPdfPage() {
                if (this.pdfPage < this.pdfTotalPages) {
                    this.pdfPage++;
                    this.renderPdfPage();
                    this.cancelCrop();
                }
            },
            zoomPdf(scaleFactor) {
                this.pdfZoom *= scaleFactor;
                this.renderPdfPage();
                this.cancelCrop();
            },
            
            // Mouse Drag PDF.js crop selection triggers
            startFragmentSelection(e) {
                if (!this.viewingAsset || this.viewingAsset.file_type !== 'pdf') return;
                this.isSelecting = true;
                const canvas = document.getElementById('pdf-render-canvas');
                const rect = canvas.getBoundingClientRect();
                this.startX = e.clientX - rect.left;
                this.startY = e.clientY - rect.top;
                
                const overlay = document.getElementById('pdf-crop-overlay');
                overlay.style.left = (e.clientX - rect.left + canvas.offsetLeft) + 'px';
                overlay.style.top = (e.clientY - rect.top + canvas.offsetTop) + 'px';
                overlay.style.width = '0px';
                overlay.style.height = '0px';
                overlay.style.display = 'block';
                
                this.cancelCrop();
            },
            
            drawFragmentSelection(e) {
                if (!this.isSelecting) return;
                const canvas = document.getElementById('pdf-render-canvas');
                const rect = canvas.getBoundingClientRect();
                const currentX = Math.max(0, Math.min(e.clientX - rect.left, canvas.width));
                const currentY = Math.max(0, Math.min(e.clientY - rect.top, canvas.height));
                
                const x = Math.min(this.startX, currentX);
                const y = Math.min(this.startY, currentY);
                const w = Math.abs(this.startX - currentX);
                const h = Math.abs(this.startY - currentY);
                
                const overlay = document.getElementById('pdf-crop-overlay');
                overlay.style.left = (x + canvas.offsetLeft) + 'px';
                overlay.style.top = (y + canvas.offsetTop) + 'px';
                overlay.style.width = w + 'px';
                overlay.style.height = h + 'px';
                
                // BBox percentage mapping
                const pctX = Math.round((x / canvas.width) * 100);
                const pctY = Math.round((y / canvas.height) * 100);
                const pctW = Math.round((w / canvas.width) * 100);
                const pctH = Math.round((h / canvas.height) * 100);
                
                this.cropCoords = { x: pctX, y: pctY, w: pctW, h: pctH };
                this.cropCoordsStr = `[x:${pctX} y:${pctY} w:${pctW} h:${pctH}]`;
            },
            
            endFragmentSelection(e) {
                if (!this.isSelecting) return;
                this.isSelecting = false;
                
                // Spawn TippyJS popup at selection coordinates
                const overlay = document.getElementById('pdf-crop-overlay');
                const canvas = document.getElementById('pdf-render-canvas');
                
                if (this.cropCoords && this.cropCoords.w > 4 && this.cropCoords.h > 4) {
                    this.cropTag = this.activeFilterTag || '';
                    
                    const tempSpan = document.createElement('span');
                    tempSpan.style.position = 'absolute';
                    tempSpan.style.left = (overlay.offsetLeft + (overlay.offsetWidth / 2)) + 'px';
                    tempSpan.style.top = (overlay.offsetTop + overlay.offsetHeight) + 'px';
                    canvas.parentElement.appendChild(tempSpan);
                    
                    const template = document.getElementById('crop-tippy-template');
                    this.tippyInstance = tippy(tempSpan, {
                        content: template.innerHTML,
                        allowHTML: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom',
                        onDestroy() {
                            tempSpan.remove();
                        }
                    });
                    
                    this.tippyInstance.show();
                    
                    // Bind event listeners to tippy inputs inside the new DOM context
                    setTimeout(() => {
                        const tippyPopper = this.tippyInstance.popper;
                        const saveBtn = tippyPopper.querySelector('.btn-primary');
                        const cancelBtn = tippyPopper.querySelector('.btn-outline-secondary');
                        const tagInput = tippyPopper.querySelector('#frag-tag');
                        const noteInput = tippyPopper.querySelector('#frag-note');
                        
                        if(tagInput) {
                            tagInput.value = this.cropTag;
                            tagInput.addEventListener('input', (e) => this.cropTag = e.target.value);
                        }
                        if(noteInput) {
                            noteInput.value = this.cropNote;
                            noteInput.addEventListener('input', (e) => this.cropNote = e.target.value);
                        }
                        if(saveBtn) saveBtn.addEventListener('click', () => this.saveCropFragment());
                        if(cancelBtn) cancelBtn.addEventListener('click', () => this.cancelCrop());
                    }, 50);
                } else {
                    this.cancelCrop();
                }
            },
            
            cancelCrop() {
                const overlay = document.getElementById('pdf-crop-overlay');
                if (overlay) overlay.style.display = 'none';
                if (this.tippyInstance) {
                    this.tippyInstance.destroy();
                    this.tippyInstance = null;
                }
                this.cropCoords = null;
                this.cropCoordsStr = '';
                this.cropTag = '';
                this.cropNote = '';
            },
            
            async saveCropFragment() {
                if (!this.cropTag.trim()) {
                    alert('Vui lòng nhập nhãn tag!');
                    return;
                }
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= csrf_token() ?>');
                formData.append('asset_id', this.viewingAsset.id);
                formData.append('page_number', this.pdfPage);
                formData.append('bounding_box', JSON.stringify(this.cropCoords));
                formData.append('tag_prefix', this.cropTag);
                formData.append('note', this.cropNote);
                
                try {
                    const res = await fetch('/asset/fragment', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (window.showToast) window.showToast('Tạo tag fragment thành công!', 'success');
                        this.cancelCrop();
                        
                        // Dynamically refresh window/tagbench
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert(data.error || 'Có lỗi xảy ra');
                    }
                } catch (err) {
                    console.error('Error saving fragment', err);
                }
            }
        };
    }
</script>
