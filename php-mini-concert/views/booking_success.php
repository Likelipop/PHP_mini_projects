<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ticket {
            background: white;
            border-radius: 20px;
            border-left: 10px solid #6366f1;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .ticket-dashed {
            border-top: 2px dashed #e2e8f0;
            position: relative;
        }
        .ticket-dashed::before, .ticket-dashed::after {
            content: '';
            position: absolute;
            top: -10px;
            width: 20px;
            height: 20px;
            background: #f8f9fa;
            border-radius: 50%;
        }
        .ticket-dashed::before { left: -10px; }
        .ticket-dashed::after { right: -10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <div class="display-1 text-success">✓</div>
                    <h2 class="fw-bold">Đặt vé thành công!</h2>
                    <p class="text-muted">Cảm ơn <?= htmlspecialchars($data['customer_name']) ?>, mã xác nhận đã được gửi đến <?= htmlspecialchars($data['email']) ?></p>
                </div>

                <div class="ticket p-0">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary">E-TICKET</span>
                            <span class="fw-bold text-muted small">#<?= $data['booking_id'] ?></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($data['concert']['title']) ?></h3>
                        <p class="text-primary mb-3">🎤 Nghệ sĩ: <?= htmlspecialchars($data['concert']['artist']) ?></p>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Ngày diễn</small>
                                <strong><?= htmlspecialchars($data['concert']['date']) ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Số lượng</small>
                                <strong><?= $data['quantity'] ?> Vé</strong>
                            </div>
                        </div>
                    </div>

                    <div class="ticket-dashed p-4 bg-light">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <small class="text-muted d-block">Địa điểm</small>
                                <strong>Sân vận động Quân khu 7, TP.HCM</strong>
                                <small class="d-block mt-2 text-muted">Thời gian đặt: <?= $data['time'] ?></small>
                            </div>
                            <div class="col-4 text-end text-muted">
                                <div class="bg-dark d-inline-block p-2 rounded">
                                    <div style="width: 50px; height: 50px; background: url('https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=<?= $data['booking_id'] ?>'); background-size: cover;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="/" class="btn btn-outline-secondary px-4">Quay lại trang chủ</a>
                    <button onclick="window.print()" class="btn btn-primary px-4">In vé</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>