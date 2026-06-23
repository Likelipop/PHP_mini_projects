<div class="card shadow-sm border border-secondary-subtle tagbench-widget" x-data="tagbenchData()" @select-tag-externally.window="selectTag($event.detail.tag)">
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
            
            <!-- Autocomplete list suggestion with assets/notes count -->
            <ul class="dropdown-menu shadow w-100 show border border-secondary-subtle" x-show="autocompleteList.length > 0 && searchQuery !== ''" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; display: block; max-height: 200px; overflow-y: auto;">
                <template x-for="tag in autocompleteList" :key="tag.prefix">
                    <li>
                        <button class="dropdown-item small py-1 d-flex justify-content-between align-items-center" @click="selectTag(tag.prefix)">
                            <span>
                                <i class="fa-solid fa-hashtag text-primary me-1 small"></i>
                                <span class="fw-bold" x-text="tag.prefix"></span>
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace xsmall" style="font-size: 0.65rem;"
                                  x-text="tag.resource_count + ' resources, ' + tag.note_count + ' notes'"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <!-- Breadcrumb representation -->
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

        <!-- Prefix Tree structure (supports 3-level folder nesting) -->
        <div class="tag-tree-container small border rounded p-2" style="max-height: 250px; overflow-y: auto;">
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
                
                <div class="border-top mt-2 pt-2">
                    <template x-for="node in treeNodes" :key="node.id">
                        <div class="ms-1">
                            <!-- Level 1 Node -->
                            <div class="d-flex align-items-center justify-content-between py-0.5">
                                <a href="#" class="text-decoration-none text-body text-truncate" :class="selectedTag === node.full_prefix ? 'text-primary fw-bold' : ''" @click.prevent="selectTag(node.full_prefix)">
                                    <i class="fa-solid fa-folder text-muted me-1 small"></i> <span x-text="node.name"></span>
                                </a>
                                <button class="btn btn-link btn-xs p-0 text-muted text-decoration-none" x-show="node.children && node.children.length > 0" @click="node.expanded = !node.expanded">
                                    <i class="fa-solid text-muted" :class="node.expanded ? 'fa-angle-down' : 'fa-angle-right'"></i>
                                </button>
                            </div>
                            
                            <!-- Level 2 Node -->
                            <div class="ps-3 border-start ms-1" x-show="node.expanded" x-transition>
                                <template x-for="child in node.children" :key="child.id">
                                    <div class="ms-1">
                                        <div class="d-flex align-items-center justify-content-between py-0.5">
                                            <a href="#" class="text-decoration-none text-body text-truncate d-block" :class="selectedTag === child.full_prefix ? 'text-primary fw-bold' : ''" @click.prevent="selectTag(child.full_prefix)">
                                                <i class="fa-solid fa-folder text-muted me-1 small"></i> <span x-text="child.name"></span>
                                            </a>
                                            <button class="btn btn-link btn-xs p-0 text-muted text-decoration-none" x-show="child.children && child.children.length > 0" @click="child.expanded = !child.expanded">
                                                <i class="fa-solid text-muted" :class="child.expanded ? 'fa-angle-down' : 'fa-angle-right'"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Level 3 Node -->
                                        <div class="ps-3 border-start ms-1" x-show="child.expanded" x-transition>
                                            <template x-for="gchild in child.children" :key="gchild.id">
                                                <div class="py-0.5">
                                                    <a href="#" class="text-decoration-none text-body text-truncate d-block" :class="selectedTag === gchild.full_prefix ? 'text-primary fw-bold' : ''" @click.prevent="selectTag(gchild.full_prefix)">
                                                        <i class="fa-regular fa-hashtag text-muted me-1 small"></i> <span x-text="gchild.name"></span>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
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
                const rawTags = <?= json_encode($tags) ?>;
                this.buildTree(rawTags);
            },
            
            get selectedTagParts() {
                return this.selectedTag ? this.selectedTag.split('/') : [];
            },
            
            buildTree(tagsList) {
                const root = { children: {} };
                tagsList.forEach(t => {
                    if (t.prefix === 'untagged') return;
                    const parts = t.prefix.split('/');
                    let current = root;
                    let prefixAccumulator = '';
                    parts.forEach((part, index) => {
                        prefixAccumulator = prefixAccumulator ? prefixAccumulator + '/' + part : part;
                        if (!current.children[part]) {
                            current.children[part] = {
                                id: prefixAccumulator,
                                name: part,
                                full_prefix: prefixAccumulator,
                                expanded: false,
                                children: {}
                            };
                        }
                        current = current.children[part];
                    });
                });
                
                const convertToList = (node) => {
                    return Object.values(node.children).map(child => {
                        return {
                            id: child.id,
                            name: child.name,
                            full_prefix: child.full_prefix,
                            expanded: false,
                            children: convertToList(child)
                        };
                    });
                };
                
                this.treeNodes = convertToList(root);
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
                
                if (prefix) {
                    const parts = prefix.split('/');
                    let accum = '';
                    parts.forEach(part => {
                        accum = accum ? accum + '/' + part : part;
                        this.expandNodeInTree(this.treeNodes, accum);
                    });
                }
                
                const event = new CustomEvent('tag-selected', { detail: { tag: prefix } });
                window.dispatchEvent(event);
            },
            
            expandNodeInTree(nodes, prefix) {
                nodes.forEach(n => {
                    if (n.full_prefix === prefix) {
                        n.expanded = true;
                    }
                    if (n.children && n.children.length > 0) {
                        this.expandNodeInTree(n.children, prefix);
                    }
                });
            },
            
            resetFilters() {
                this.selectTag('');
            }
        };
    }
</script>
