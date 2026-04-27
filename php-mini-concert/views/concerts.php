<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title><?= htmlspecialchars($data['title']) ?> - Concert Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    
    <div class="mb-3">
        <a href="/" class="text-decoration-none text-secondary fw-medium hover-primary">
            &larr; Quay về trang chủ
        </a>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h2 fw-bold">Khám phá tất cả sự kiện</h1>
        </div>
        
        <div class="col-md-6">
            <form action="/concerts" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" 
                       placeholder="Tìm tên sự kiện..." 
                       value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>">
                
                <select name="status" class="form-select" style="width: auto;">
                    <option value="all" <?= ($data['filters']['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                    <option value="available" <?= ($data['filters']['status'] ?? '') === 'available' ? 'selected' : '' ?>>Còn vé</option>
                    <option value="soldout" <?= ($data['filters']['status'] ?? '') === 'soldout' ? 'selected' : '' ?>>Đã hết vé</option>
                </select>
                
                <button type="submit" class="btn btn-dark">Lọc</button>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if (empty($data['concerts'])): ?>
            <div class="col-12 text-center py-5">
                <h3 class="text-muted mb-3">😕 Ôi không!</h3>
                <p class="text-muted">Không tìm thấy sự kiện nào phù hợp với bộ lọc của bạn.</p>
                <a href="/concerts" class="btn btn-outline-primary mt-2">Xóa bộ lọc và xem tất cả</a>
            </div>
        <?php else: ?>
            <?php foreach ($data['concerts'] as $concert): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 concert-card shadow-sm border-0 position-relative">
                        
                        <div class="position-relative overflow-hidden" style="border-radius: 15px 15px 0 0;">
                            <img src="<?= $concert['image'] ?? 'https://images.pexels.com/photos/4183059/pexels-photo-4183059.jpeg' ?>" 
                                 class="card-img-top w-100" 
                                 alt="Concert Cover" 
                                 style="height: 180px; object-fit: cover;">
                            
                            <?php if ($concert['seats_available'] <= 0): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">Sold Out</span>
                            <?php else: ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">Đang mở bán</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            
                            <h5 class="card-title fw-bold text-dark mb-3 text-truncate" title="<?= htmlspecialchars($concert['title']) ?>">
                                <?= htmlspecialchars($concert['title']) ?>
                            </h5>
                            
                            <div class="text-muted small mb-3">
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="me-2 fs-6">📅</span> 
                                    <?= htmlspecialchars($concert['date'] ?? 'Đang cập nhật lịch diễn') ?>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="me-2 fs-6">📍</span> 
                                    <?= htmlspecialchars($concert['location'] ?? 'Sân vận động Quốc gia') ?>
                                </div>
                            </div>

                            <div class="mt-auto mb-4">
                                <?php 
                                    // Tính phần trăm vé đã bán
                                    $total = $concert['seats_total'] > 0 ? $concert['seats_total'] : 1;
                                    $sold = $total - $concert['seats_available'];
                                    $percent = round(($sold / $total) * 100);
                                ?>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-secondary fw-medium">Đã bán <?= $percent ?>%</span>
                                    <span class="fw-bold text-dark"><?= $concert['seats_available'] ?> / <?= $concert['seats_total'] ?> còn lại</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                    <div class="progress-bar <?= $concert['seats_available'] <= 0 ? 'bg-danger' : 'bg-primary' ?>" 
                                         role="progressbar" 
                                         style="width: <?= $percent ?>%" 
                                         aria-valuenow="<?= $percent ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-book w-100 py-2 <?= $concert['seats_available'] <= 0 ? 'disabled bg-secondary border-secondary' : '' ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#bookingModal"
                                    data-concert-id="<?= $concert['id'] ?>"
                                    data-concert-title="<?= htmlspecialchars($concert['title']) ?>">
                                <?= $concert['seats_available'] <= 0 ? 'Đã Hết Vé' : '🎫 Đặt Vé Ngay' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require __DIR__ . '/partials/booking_modal.php'; ?>

</body>
</html>