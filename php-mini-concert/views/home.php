<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?> - Concert Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --concert-primary: #6366f1; }
        body { font-family: 'Inter', sans-serif; }
        .concert-card { 
            transition: all 0.3s ease; 
            border: none; 
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .concert-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
        }
        .badge-status { 
            position: absolute; 
            top: 15px; 
            right: 15px; 
            padding: 6px 12px;
            border-radius: 20px;
        }
        .btn-book {
            background-color: var(--concert-primary);
            color: white;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-book:hover {
            background-color: #4f46e5;
            color: white;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm mb-5">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <span class="fs-3 me-2">🎸</span>
            <div>
                <div class="fw-bold lh-1"><?= htmlspecialchars($data['app_name']) ?></div>
                <small class="text-secondary" style="font-size: 0.7rem;">by <?= htmlspecialchars($data['organizer']) ?></small>
            </div>
        </a>
        <div class="d-flex">
            <span class="badge rounded-pill bg-outline-light border border-secondary text-secondary small">
                <?= htmlspecialchars($data['app_env']) ?>
            </span>
        </div>
    </div>
</nav>

<div class="container">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bolder text-dark mb-2"><?= htmlspecialchars($data['title']) ?></h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">
            Chào mừng Bạn! Khám phá và đặt vé cho những sự kiện âm nhạc bùng nổ nhất năm 2026.
        </p>
    </div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-0">Upcoming Concerts</h2>
        <span class="text-muted small">Showing <?= count($data['concerts']) ?> shows</span>
    </div>
    <a href="/concerts" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        Xem tất cả &rarr;
    </a>
</div>

<div class="row">
    <?php foreach ($data['concerts'] as $concert): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 concert-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Tickets left</div>
                            <div class="fw-bold"><?= $concert['seats_available'] ?> / <?= $concert['seats_total'] ?></div>
                        </div>
                        
                        <button class="btn btn-book btn-sm <?= $concert['seats_available'] <= 0 ? 'disabled' : '' ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#bookingModal"
                                data-concert-id="<?= $concert['id'] ?>"
                                data-concert-title="<?= htmlspecialchars($concert['title']) ?>">
                            <?= $concert['seats_available'] <= 0 ? 'Sold Out' : 'Book Now' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Xác nhận đặt vé</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/bookings" method="POST">
                <div class="modal-body p-4">
                    <p class="text-muted">Bạn đang đặt vé cho: <strong id="modalConcertTitle" class="text-primary"></strong></p>
                    
                    <input type="hidden" name="concert_id" id="modalConcertId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên</label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập tên của bạn" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nguyenvan@example.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số lượng vé</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="10" required>
                        <div class="form-text">Tối đa 10 vé mỗi giao dịch.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Xác nhận thanh toán</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/booking_modal.php'; ?>

    <footer class="mt-5 py-5 border-top">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="text-muted mb-0">&copy; 2026 <strong><?= htmlspecialchars($data['app_name']) ?></strong>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <span class="me-3 small text-secondary">API Status: <span class="text-success">● Online</span></span>
                <code class="p-2 bg-white rounded border small">POST /bookings</code>
            </div>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>