<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?> - Workshop Hub</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .navbar {
            background: var(--primary-gradient);
        }
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15);
        }
        .card-custom {
            border: none;
            border-radius: 1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .badge-status {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }
        .endpoint-badge {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold gap-2" href="#">
                <i class="bi bi-laptop fs-3"></i>
                <div>
                    <span class="lh-1 d-block"><?= htmlspecialchars($data['app_name']) ?></span>
                </div>
            </a>
            <span class="badge bg-light text-dark fw-medium border">
                Env: <span class="text-uppercase fw-bold text-indigo"><?= htmlspecialchars($data['app_env']) ?></span>
            </span>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-extrabold mb-3"><?= htmlspecialchars($data['title']) ?></h1>
            <p class="lead opacity-90 mx-auto" style="max-width: 600px;">
                Enhance your development skills by joining HCMUS premium technology workshops.
            </p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <span class="badge bg-white bg-opacity-25 px-3 py-2">Organizer: <?= htmlspecialchars($data['organizer']) ?></span>
                <span class="badge bg-white bg-opacity-25 px-3 py-2">Debug Mode: <?= $data['app_debug'] ? 'ON' : 'OFF' ?></span>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row g-4">
            <!-- Left Side: Workshop List -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h3 fw-bold text-gray-800 m-0">Available Workshops</h2>
                    <span class="text-muted fw-medium"><?= count($data['events']) ?> events found</span>
                </div>

                <div class="row g-3">
                    <?php foreach ($data['events'] as $event): ?>
                        <div class="col-md-6">
                            <div class="card h-100 card-custom">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h3 class="h5 fw-bold mb-0 text-dark"><?= htmlspecialchars($event['title']) ?></h3>
                                            <?php if ($event['seats_available'] > 0): ?>
                                                <span class="badge bg-success-subtle text-success badge-status">Open</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger badge-status">Full</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3 text-muted small">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-person-workspace text-primary"></i>
                                                <span>Trainer: <strong><?= htmlspecialchars($event['trainer']) ?></strong></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-calendar-event text-primary"></i>
                                                <span>Date: <?= htmlspecialchars($event['date']) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top pt-3 mt-3">
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>Available Seats</span>
                                            <span class="fw-bold text-dark"><?= $event['seats_available'] ?> / <?= $event['seats_total'] ?></span>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <?php 
                                            $percentage = ($event['seats_total'] > 0) ? (($event['seats_total'] - $event['seats_available']) / $event['seats_total']) * 100 : 0;
                                            ?>
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Side: API Documentation -->
            <div class="col-lg-4">
                <div class="card card-custom border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-code-slash text-primary"></i>
                            API Reference
                        </h4>
                        <p class="text-muted small">Quickly check endpoints available for third-party client integrations.</p>
                        
                        <div class="d-grid gap-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="badge bg-success endpoint-badge me-2">GET</span>
                                <code class="small">/events</code>
                            </div>
                            <div class="p-2 border rounded bg-white">
                                <span class="badge bg-info text-dark endpoint-badge me-2">HEAD</span>
                                <code class="small">/events</code>
                            </div>
                            <div class="p-2 border rounded bg-white">
                                <span class="badge bg-warning text-dark endpoint-badge me-2">POST</span>
                                <code class="small">/registrations</code>
                            </div>
                            <div class="p-2 border rounded bg-white">
                                <span class="badge bg-secondary endpoint-badge me-2">OPTIONS</span>
                                <code class="small">/registrations</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>