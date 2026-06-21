<div class="card shadow-sm border border-secondary-subtle vscode-sidebar" x-data="{ expanded: { readme: true, notes: true, resources: true } }">
    <div class="card-header bg-body-tertiary fw-bold py-2 small">
        <i class="fa-solid fa-list-check text-primary me-2"></i> Workspace Outline
    </div>
    
    <div class="card-body p-0">
        <div class="list-group list-group-flush small">
            
            <!-- README Node -->
            <div class="list-group-item p-0 border-light-subtle">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-secondary-hover cursor-pointer" @click="expanded.readme = !expanded.readme">
                    <span class="fw-semibold text-body"><i class="fa-solid fa-angle-right me-1 text-muted transition-rotate" :class="expanded.readme ? 'rotate-90' : ''"></i> README</span>
                </div>
                <div class="ps-4 py-1 border-top-0" x-show="expanded.readme" x-transition>
                    <a href="#" class="d-block text-decoration-none text-body-secondary py-1" @click.prevent="activeSection = 'readme'">
                        <i class="fa-regular fa-file-lines me-1 text-primary"></i> README.md
                    </a>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="list-group-item p-0 border-light-subtle">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-secondary-hover cursor-pointer" @click="expanded.notes = !expanded.notes">
                    <span class="fw-semibold text-body"><i class="fa-solid fa-angle-right me-1 text-muted transition-rotate" :class="expanded.notes ? 'rotate-90' : ''"></i> Ghi chú (Notes)</span>
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill xsmall"><?= count($notes) ?></span>
                </div>
                <div class="ps-4 py-1 border-top-0" x-show="expanded.notes" x-transition style="max-height: 200px; overflow-y: auto;">
                    <?php if (!empty($notes)): ?>
                        <?php foreach ($notes as $note): ?>
                            <a href="#" class="d-block text-decoration-none text-body-secondary py-1 note-outline-link text-truncate" data-id="<?= $note['id'] ?>" @click.prevent="activeSection = 'workspace'; setTimeout(() => { document.getElementById('notes-tab').click(); const card = document.querySelector(`.note-list-card[data-id='<?= $note['id'] ?>']`); if(card) card.click(); }, 100)">
                                <i class="fa-solid fa-note-sticky me-1 text-warning"></i> <?= h($note['title']) ?>.md
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted d-block py-1 xsmall">Chưa có ghi chú</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resources Folders Section -->
            <div class="list-group-item p-0 border-light-subtle">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-secondary-hover cursor-pointer" @click="expanded.resources = !expanded.resources">
                    <span class="fw-semibold text-body"><i class="fa-solid fa-angle-right me-1 text-muted transition-rotate" :class="expanded.resources ? 'rotate-90' : ''"></i> Tài nguyên (Resources)</span>
                </div>
                <div class="ps-4 py-1 border-top-0" x-show="expanded.resources" x-transition>
                    <a href="#" class="d-block text-decoration-none text-body-secondary py-1" @click.prevent="activeSection = 'workspace'; setTimeout(() => { document.getElementById('resources-tab').click(); const accBtn = document.getElementById('headingSlides').querySelector('button'); if(accBtn && accBtn.classList.contains('collapsed')) accBtn.click(); }, 100)">
                        <i class="fa-regular fa-folder-open me-1 text-warning"></i> Slides
                    </a>
                    <a href="#" class="d-block text-decoration-none text-body-secondary py-1" @click.prevent="activeSection = 'workspace'; setTimeout(() => { document.getElementById('resources-tab').click(); const accBtn = document.getElementById('headingImages').querySelector('button'); if(accBtn && accBtn.classList.contains('collapsed')) accBtn.click(); }, 100)">
                        <i class="fa-regular fa-folder-open me-1 text-success"></i> Images
                    </a>
                    <a href="#" class="d-block text-decoration-none text-body-secondary py-1" @click.prevent="activeSection = 'workspace'; setTimeout(() => { document.getElementById('resources-tab').click(); const accBtn = document.getElementById('headingAssignments').querySelector('button'); if(accBtn && accBtn.classList.contains('collapsed')) accBtn.click(); }, 100)">
                        <i class="fa-regular fa-folder-open me-1 text-info"></i> Assignments
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
