<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                <li class="breadcrumb-item active">Danh sách Concert</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h3 class="card-title mb-0 fw-bold">📅 Lịch Biểu Diễn Chi Tiết</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Sự Kiện</th>
                                <th>Nghệ Sĩ</th>
                                <th>Ngày Diễn</th>
                                <th>Tình Trạng Vé</th>
                                <th class="text-end">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['concerts'] as $concert): ?>
                            <tr>
                                <td class="text-muted">#<?= $concert['id'] ?></td>
                                <td><strong><?= htmlspecialchars($concert['title']) ?></strong></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($concert['artist']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($concert['date'])) ?></td>
                                <td>
                                    <?php if ($concert['seats_available'] > 0): ?>
                                        <div class="progress" style="height: 10px; width: 100px;">
                                            <?php $percent = ($concert['seats_available'] / $concert['seats_total']) * 100; ?>
                                            <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <small class="text-muted">Còn <?= $concert['seats_available'] ?> vé</small>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold small">Hết vé</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-primary <?= $concert['seats_available'] <= 0 ? 'disabled' : '' ?>">
                                        Đặt ngay
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>