<div class="row g-3" x-data="resourceGridData()">
    <!-- Left side: folder lists and files grid -->
    <div class="col-md-7 d-flex flex-column gap-3">
        <!-- Search and Sort bar -->
        <div class="card shadow-sm border border-secondary-subtle">
            <div class="card-body py-2 px-3 d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm flex-grow-1">
                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Lọc tài nguyên trong StudyFlow..." 
                           x-model="searchQuery">
                </div>
                <div class="d-flex align-items-center gap-1">
                    <select class="form-select form-select-sm" x-model="sortBy" style="width: 105px; font-size: 0.75rem;">
                        <option value="newest">Mới nhất</option>
                        <option value="oldest">Cũ nhất</option>
                        <option value="name">Tên A-Z</option>
                    </select>
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
                            <template x-for="res in paginatedSlides" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border resource-card" 
                                         :class="viewingAsset && viewingAsset.id === res.id ? 'border-primary bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle'"
                                         @click="viewAsset(res)">
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
                        <!-- Pagination slides -->
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1" x-show="slidesTotalPages > 1">
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="slidesPage === 1" @click="slidesPage--"><i class="fa-solid fa-angle-left"></i> Trước</button>
                            <span class="xsmall text-muted" x-text="slidesPage + ' / ' + slidesTotalPages"></span>
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="slidesPage === slidesTotalPages" @click="slidesPage++">Sau <i class="fa-solid fa-angle-right"></i></button>
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
                            <template x-for="res in paginatedImages" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border resource-card" 
                                         :class="viewingAsset && viewingAsset.id === res.id ? 'border-primary bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle'"
                                         @click="viewAsset(res)">
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
                        <!-- Pagination images -->
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1" x-show="imagesTotalPages > 1">
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="imagesPage === 1" @click="imagesPage--"><i class="fa-solid fa-angle-left"></i> Trước</button>
                            <span class="xsmall text-muted" x-text="imagesPage + ' / ' + imagesTotalPages"></span>
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="imagesPage === imagesTotalPages" @click="imagesPage++">Sau <i class="fa-solid fa-angle-right"></i></button>
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
                            <template x-for="res in paginatedAssignments" :key="res.id">
                                <div class="col-6 col-sm-4 res-card-wrapper" :data-id="res.id" :data-tags="res.tags.join(',')">
                                    <div class="card h-100 p-2 shadow-xs border resource-card" 
                                         :class="viewingAsset && viewingAsset.id === res.id ? 'border-primary bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle'"
                                         @click="viewAsset(res)">
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
                        <!-- Pagination assignments -->
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1" x-show="assignmentsTotalPages > 1">
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="assignmentsPage === 1" @click="assignmentsPage--"><i class="fa-solid fa-angle-left"></i> Trước</button>
                            <span class="xsmall text-muted" x-text="assignmentsPage + ' / ' + assignmentsTotalPages"></span>
                            <button class="btn btn-link btn-xs p-0 text-decoration-none text-muted" :disabled="assignmentsPage === assignmentsTotalPages" @click="assignmentsPage++">Sau <i class="fa-solid fa-angle-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side: Asset Viewer (PDF.js Canvas & Bounding Box highlight) -->
    <div class="col-md-5 d-flex flex-column border-start ps-3" style="max-height: calc(100vh - 160px);">
        <div class="card shadow-sm border border-secondary-subtle flex-grow-1 d-flex flex-column">
            <div class="card-header bg-body-tertiary fw-bold py-2.5 small d-flex justify-content-between align-items-center">
                <span><i class="fa-regular fa-eye text-primary me-2"></i> Trình xem tài liệu</span>
                <span class="badge bg-secondary-subtle text-secondary border font-monospace xsmall" x-text="viewingAsset ? viewingAsset.title : 'Chưa chọn file'"></span>
            </div>
            
            <div class="card-body p-2 d-flex flex-column justify-content-between overflow-hidden position-relative bg-dark-subtle" id="viewer-viewport" style="min-height: 380px;">
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

            <!-- Metadata and Tag Area under preview panel -->
            <div class="card-footer bg-body-tertiary border-top p-2" x-show="viewingAsset" x-transition style="display: none;">
                <div class="xsmall text-muted mb-2 border-bottom pb-2">
                    <div class="row g-1">
                        <div class="col-4 fw-bold">Tên tệp:</div>
                        <div class="col-8 text-truncate font-monospace" x-text="viewingAsset.title"></div>
                        
                        <div class="col-4 fw-bold">Kích thước:</div>
                        <div class="col-8" x-text="formatBytes(viewingAsset.file_size)"></div>
                        
                        <div class="col-4 fw-bold">Ngày tạo:</div>
                        <div class="col-8" x-text="new Date(viewingAsset.created_at).toLocaleDateString('vi-VN')"></div>
                        
                        <div class="col-4 fw-bold">Trạng thái:</div>
                        <div class="col-8">
                            <span class="badge bg-secondary-subtle text-secondary border xsmall font-monospace" x-text="viewingAsset.tags.length === 0 || (viewingAsset.tags.length === 1 && viewingAsset.tags[0] === 'untagged') ? 'untagged' : 'tagged'"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tag list and Add Tag Autocomplete Form -->
                <div>
                    <div class="fw-bold xsmall mb-1 text-muted"><i class="fa-solid fa-tags me-1"></i> Tags</div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <template x-for="tag in viewingAsset.tags" :key="tag">
                            <span class="badge bg-primary-subtle text-primary border xsmall d-flex align-items-center gap-1 font-monospace" style="font-size: 0.7rem;">
                                <span x-text="tag"></span>
                                <button class="btn-close" style="font-size: 0.5rem;" @click.prevent="removeTagFromAsset(tag)"></button>
                            </span>
                        </template>
                        <span x-show="viewingAsset.tags.length === 0 || (viewingAsset.tags.length === 1 && viewingAsset.tags[0] === 'untagged')" class="xsmall text-muted font-monospace italic">untagged</span>
                    </div>
                    
                    <!-- Autocomplete Tag Input -->
                    <div class="position-relative">
                        <div class="input-group input-group-xs">
                            <input type="text" class="form-control form-control-xs py-0.5 bg-body" placeholder="Thêm tag nhanh..." 
                                   x-model="newTagInput" @input="fetchTagSuggestions()" @keydown.enter.prevent="addTagToAsset(newTagInput)">
                            <button class="btn btn-xs btn-outline-primary" @click="addTagToAsset(newTagInput)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <ul class="dropdown-menu shadow w-100 show border border-secondary-subtle" x-show="tagSuggestions.length > 0 && newTagInput !== ''" style="position: absolute; bottom: 100%; left: 0; right: 0; z-index: 1050; display: block; max-height: 150px; overflow-y: auto;">
                            <template x-for="tag in tagSuggestions" :key="tag.prefix">
                                <li>
                                    <button class="dropdown-item small py-1" @click="addTagToAsset(tag.prefix)">
                                        <i class="fa-solid fa-hashtag text-primary me-1 small"></i> <span x-text="tag.prefix"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
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
                <input type="text" id="frag-tag" class="form-control form-control-xs" placeholder="ML/CNN...">
            </div>
            <div class="mb-3">
                <label for="frag-note" class="xsmall fw-semibold text-muted mb-0.5">Ghi chú (Note)</label>
                <textarea id="frag-note" class="form-control form-control-xs" rows="2" placeholder="Nội dung note..."></textarea>
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
            sortBy: 'newest',
            activeFilterTag: '',
            resources: [],
            dragOver: false,
            uploadProgress: 0,
            viewingAsset: null,
            
            // Client-side pagination config
            pageSize: 6,
            slidesPage: 1,
            imagesPage: 1,
            assignmentsPage: 1,

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

            // Tag modification inputs
            newTagInput: '',
            tagSuggestions: [],

            init() {
                this.resources = <?= json_encode($resources) ?>;
                
                // Listen to TagBench tag selection events
                window.addEventListener('tag-selected', (e) => {
                    this.activeFilterTag = e.detail.tag;
                    // Reset pagination when active filter changes
                    this.slidesPage = 1;
                    this.imagesPage = 1;
                    this.assignmentsPage = 1;
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
            
            applyFiltersAndSort(list) {
                let filtered = list.filter(r => {
                    const matchesSearch = r.title.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchesTag = !this.activeFilterTag || 
                                       (this.activeFilterTag === 'untagged' && (r.tags.length === 0 || (r.tags.length === 1 && r.tags[0] === 'untagged'))) ||
                                       r.tags.some(t => t === this.activeFilterTag || t.startsWith(this.activeFilterTag + '/'));
                    return matchesSearch && matchesTag;
                });
                
                // Sort list
                if (this.sortBy === 'newest') {
                    filtered.sort((a, b) => b.id - a.id);
                } else if (this.sortBy === 'oldest') {
                    filtered.sort((a, b) => a.id - b.id);
                } else if (this.sortBy === 'name') {
                    filtered.sort((a, b) => a.title.localeCompare(b.title));
                }
                
                return filtered;
            },
            
            // Pagination selectors
            get paginatedSlides() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type === 'pdf'));
                const start = (this.slidesPage - 1) * this.pageSize;
                return list.slice(start, start + this.pageSize);
            },
            get slidesTotalPages() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type === 'pdf'));
                return Math.max(1, Math.ceil(list.length / this.pageSize));
            },
            
            get paginatedImages() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type === 'image'));
                const start = (this.imagesPage - 1) * this.pageSize;
                return list.slice(start, start + this.pageSize);
            },
            get imagesTotalPages() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type === 'image'));
                return Math.max(1, Math.ceil(list.length / this.pageSize));
            },
            
            get paginatedAssignments() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type !== 'pdf' && r.file_type !== 'image'));
                const start = (this.assignmentsPage - 1) * this.pageSize;
                return list.slice(start, start + this.pageSize);
            },
            get assignmentsTotalPages() {
                const list = this.applyFiltersAndSort(this.resources.filter(r => r.file_type !== 'pdf' && r.file_type !== 'image'));
                return Math.max(1, Math.ceil(list.length / this.pageSize));
            },
            
            formatBytes(bytes) {
                if (!bytes) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            },
            
            // S3 MinIO Uploads
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
                            // Hydrate file_type
                            const mime = data.asset.mime_type.toLowerCase();
                            if (mime.includes('image')) {
                                data.asset.file_type = 'image';
                            } else if (mime.includes('pdf') || data.asset.filename.toLowerCase().endsWith('.pdf')) {
                                data.asset.file_type = 'pdf';
                            } else {
                                data.asset.file_type = 'other';
                            }
                            this.resources.push(data.asset);
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
            
            // View Asset
            viewAsset(asset) {
                this.viewingAsset = asset;
                this.cancelCrop();
                this.newTagInput = '';
                this.tagSuggestions = [];
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
            
            // Tags suggestions and edits inside detail footer
            async fetchTagSuggestions() {
                if (this.newTagInput.trim() === '') {
                    this.tagSuggestions = [];
                    return;
                }
                try {
                    const response = await fetch(`/api/tags/search?q=${encodeURIComponent(this.newTagInput)}`);
                    this.tagSuggestions = await response.json();
                } catch (err) {
                    console.error('Error fetching tag suggestions', err);
                }
            },
            
            async addTagToAsset(prefix) {
                const cleanPrefix = prefix.trim();
                if (!cleanPrefix) return;
                
                if (this.viewingAsset.tags.includes(cleanPrefix)) {
                    this.newTagInput = '';
                    this.tagSuggestions = [];
                    return;
                }
                
                const updatedTags = [...this.viewingAsset.tags.filter(t => t !== 'untagged'), cleanPrefix];
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= csrf_token() ?>');
                formData.append('tags', updatedTags.join(','));
                
                try {
                    const response = await fetch(`/api/assets/${this.viewingAsset.id}/tags`, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.viewingAsset.tags = updatedTags;
                        const resObj = this.resources.find(r => r.id === this.viewingAsset.id);
                        if (resObj) {
                            resObj.tags = updatedTags;
                        }
                        this.newTagInput = '';
                        this.tagSuggestions = [];
                        if (window.showToast) window.showToast('Thêm tag thành công!', 'success');
                    }
                } catch (err) {
                    console.error('Error adding tag to asset', err);
                }
            },
            
            async removeTagFromAsset(prefix) {
                const updatedTags = this.viewingAsset.tags.filter(t => t !== prefix);
                const finalTags = updatedTags.length > 0 ? updatedTags : ['untagged'];
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= csrf_token() ?>');
                formData.append('tags', finalTags.join(','));
                
                try {
                    const response = await fetch(`/api/assets/${this.viewingAsset.id}/tags`, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.viewingAsset.tags = finalTags;
                        const resObj = this.resources.find(r => r.id === this.viewingAsset.id);
                        if (resObj) {
                            resObj.tags = finalTags;
                        }
                        if (window.showToast) window.showToast('Xóa tag thành công!', 'success');
                    }
                } catch (err) {
                    console.error('Error removing tag from asset', err);
                }
            },

            // PDF Crop drawing
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
