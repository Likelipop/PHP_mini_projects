<div class="card shadow-sm border border-secondary-subtle tagbench-widget" x-data="tagbenchData()">
    <div class="card-header bg-body-tertiary fw-bold py-2 small d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-tags text-primary me-2"></i> TagBench</span>
        <button class="btn btn-link p-0 text-muted btn-xs text-decoration-none" @click="resetFilters()" title="Reset Filters">
            <i class="fa-solid fa-rotate-left"></i>
        </button>
    </div>
    
    <div class="card-body p-2 d-flex flex-column gap-2">
        <!-- Autocomplete tags filter search -->
        <div class="position-relative">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Lọc tag nhanh..." 
                       x-model="searchQuery" @input="fetchAutocomplete()" id="tagbench-search-input">
            </div>
            
            <!-- TippyJS autocomplete list content anchor -->
            <ul class="dropdown-menu shadow w-100 show border border-secondary-subtle" x-show="autocompleteList.length > 0 && searchQuery !== ''" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; display: block; max-height: 200px; overflow-y: auto;">
                <template x-for="tag in autocompleteList" :key="tag.prefix">
                    <li>
                        <button class="dropdown-item small py-1" @click="selectTag(tag.prefix)">
                            <i class="fa-solid fa-hashtag text-primary me-1 small"></i> <span x-text="tag.prefix"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <!-- Breadcrumb representation (Obsidian breadcrumbs UI) -->
        <nav aria-label="breadcrumb" x-show="selectedTag !== ''">
            <ol class="breadcrumb bg-body-secondary p-2 rounded mb-0 xsmall border font-monospace">
                <li class="breadcrumb-item"><a href="#" @click.prevent="resetFilters()" class="text-decoration-none text-primary">All</a></li>
                <template x-for="(part, index) in selectedTagParts" :key="index">
                    <li class="breadcrumb-item active" :class="index === selectedTagParts.length - 1 ? 'fw-bold' : ''">
                        <span x-text="part"></span>
                    </li>
                </template>
            </ol>
        </nav>

        <!-- Prefix Tree structure -->
        <div class="tag-tree-container small border rounded p-2" style="max-height: 250px; overflow-y: auto;">
            <!-- Tree node list -->
            <ul class="list-unstyled mb-0" id="tagbench-root-list">
                <li class="py-1">
                    <a href="#" class="text-decoration-none text-body fw-semibold" @click.prevent="selectTag('')" :class="selectedTag === '' ? 'text-primary' : ''">
                        <i class="fa-solid fa-cube text-muted me-1"></i> Tất cả Assets
                    </a>
                </li>
                <li class="py-1">
                    <a href="#" class="text-decoration-none text-body fw-semibold" @click.prevent="selectTag('untagged')" :class="selectedTag === 'untagged' ? 'text-primary' : ''">
                        <i class="fa-regular fa-hashtag text-muted me-1"></i> Untagged (Mặc định)
                    </a>
                </li>
                
                <!-- Tree hierarchy nodes -->
                <div class="border-top mt-2 pt-2">
                    <template x-for="node in treeNodes" :key="node.id">
                        <div class="ms-1">
                            <div class="d-flex align-items-center justify-content-between py-0.5">
                                <a href="#" class="text-decoration-none text-body" :class="selectedTag === node.full_prefix ? 'text-primary fw-bold' : ''" @click.prevent="selectTag(node.full_prefix)">
                                    <i class="fa-solid fa-hashtag text-muted me-1 small"></i> <span x-text="node.name"></span>
                                </a>
                                <button class="btn btn-link btn-xs p-0 text-muted text-decoration-none" x-show="node.children && node.children.length > 0" @click="node.expanded = !node.expanded">
                                    <i class="fa-solid text-muted" :class="node.expanded ? 'fa-angle-down' : 'fa-angle-right'"></i>
                                </button>
                            </div>
                            <!-- Nested children -->
                            <div class="ps-3 border-start ms-1" x-show="node.expanded" x-transition>
                                <template x-for="child in node.children" :key="child.id">
                                    <div class="py-0.5">
                                        <a href="#" class="text-decoration-none text-body text-truncate d-block" :class="selectedTag === child.full_prefix ? 'text-primary fw-bold' : ''" @click.prevent="selectTag(child.full_prefix)">
                                            <i class="fa-regular fa-hashtag text-muted me-1 small"></i> <span x-text="child.name"></span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </ul>
        </div>
    </div>
</div>

<script>
    function tagbenchData() {
        return {
            searchQuery: '',
            autocompleteList: [],
            selectedTag: '',
            treeNodes: [],
            
            init() {
                // Populate treeNodes from database tag listing injected
                const rawTags = <?= json_encode($tags) ?>;
                this.buildTree(rawTags);
            },
            
            get selectedTagParts() {
                return this.selectedTag ? this.selectedTag.split('/') : [];
            },
            
            buildTree(tagsList) {
                const roots = {};
                tagsList.forEach(t => {
                    if (t.prefix === 'untagged') return;
                    const parts = t.prefix.split('/');
                    const rootName = parts[0];
                    
                    if (!roots[rootName]) {
                        roots[rootName] = {
                            id: rootName,
                            name: rootName,
                            full_prefix: rootName,
                            expanded: false,
                            children: []
                        };
                    }
                    
                    if (parts.length > 1) {
                        const childName = parts.slice(1).join('/');
                        roots[rootName].children.push({
                            id: t.prefix,
                            name: childName,
                            full_prefix: t.prefix
                        });
                    }
                });
                
                this.treeNodes = Object.values(roots);
            },
            
            async fetchAutocomplete() {
                if (this.searchQuery.trim() === '') {
                    this.autocompleteList = [];
                    return;
                }
                try {
                    const response = await fetch(`/api/tags/search?q=${encodeURIComponent(this.searchQuery)}`);
                    this.autocompleteList = await response.json();
                } catch (err) {
                    console.error('Error fetching autocomplete tags', err);
                }
            },
            
            selectTag(prefix) {
                this.selectedTag = prefix;
                this.searchQuery = '';
                this.autocompleteList = [];
                
                // Fire custom event to update active list filters or trigger HTMX swap
                const event = new CustomEvent('tag-selected', { detail: { tag: prefix } });
                window.dispatchEvent(event);
            },
            
            resetFilters() {
                this.selectTag('');
            }
        };
    }
</script>
