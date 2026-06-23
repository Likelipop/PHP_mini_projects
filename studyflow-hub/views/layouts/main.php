<!DOCTYPE html>
<html lang="vi" x-data="{ theme: Alpine.store('theme') || 'dark' }" :data-bs-theme="theme" :class="'theme-' + theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= isset($title) ? h($title) . ' - StudyFlow Hub' : 'StudyFlow Hub - AI-powered Study Graph' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Tippy.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
    
    <!-- Custom styling -->
    <link rel="stylesheet" href="/css/style.css">
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    
    <!-- AlpineJS & Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Popper.js & Tippy.js (tooltip package) -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    
    <!-- markdown-it for note rendering -->
    <script src="https://cdn.jsdelivr.net/npm/markdown-it@14.1.0/dist/markdown-it.min.js"></script>
    
    <!-- Mermaid for diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10.9.1/dist/mermaid.min.js"></script>
    
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        // Setup Alpine store for persistent themes
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', localStorage.getItem('studyflow-theme') || 'dark');
        });
        
        function toggleTheme(store) {
            const current = store.theme;
            const next = current === 'dark' ? 'light' : 'dark';
            store.theme = next;
            localStorage.setItem('studyflow-theme', next);
        }
    </script>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Global Navbar Component -->
    <?php require __DIR__ . '/../components/navbar.php'; ?>

    <!-- Main Container -->
    <main class="flex-grow-1 container-fluid px-0">
        <?php require $viewPath; ?>
    </main>

    <!-- Global Toasts component for system status -->
    <?php require __DIR__ . '/../components/toast.php'; ?>

    <footer class="py-3 border-top bg-body-tertiary">
        <div class="container text-center text-muted small">
            &copy; 2026 StudyFlow Hub. Built using Github + Obsidian + Notion workflows.
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts -->
    <script src="/js/app.js"></script>
</body>
</html>
