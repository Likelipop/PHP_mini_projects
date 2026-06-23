<?php
$tagNames = array_column($stats['top_tags'] ?? [], 'name');
$tagCounts = array_map('intval', array_column($stats['top_tags'] ?? [], 'count'));
if (empty($tagNames)) {
    $tagNames = ['Chưa có tag nào'];
    $tagCounts = [0];
    $tagColors = ['#6c757d'];
} else {
    $tagColors = ['#0969da', '#238636', '#d29922', '#8a63d2', '#f7786b'];
}
?>
<div class="container py-4" x-data="profileDashboardData()">
    <!-- Profile header -->
    <div class="card shadow-sm border border-secondary-subtle mb-4 p-4" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(9, 105, 218, 0.04) 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <h2 class="fw-bold mb-1" style="font-family: var(--font-heading);"><?= h($_SESSION['username'] ?? 'User Profile') ?></h2>
                <p class="text-muted small mb-0"><i class="fa-regular fa-envelope me-1"></i> Email đăng ký: <code><?= h($_SESSION['email'] ?? 'user@studyflow.edu') ?></code></p>
            </div>
        </div>
    </div>

    <!-- Analytics Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border border-secondary-subtle h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="xsmall text-muted fw-bold">TÀI NGUYÊN (S3)</span>
                        <span class="text-primary"><i class="fa-solid fa-file-shield"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0"><?= (int)($stats['resources_count'] ?? 0) ?></h3>
                    <p class="xsmall text-muted mb-0 mt-1">Đã tải lên MinIO/S3</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border border-secondary-subtle h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="xsmall text-muted fw-bold">GHI CHÚ (OBSIDIAN)</span>
                        <span class="text-warning"><i class="fa-regular fa-clipboard"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0"><?= (int)($stats['notes_count'] ?? 0) ?></h3>
                    <p class="xsmall text-muted mb-0 mt-1">Ghi chú Markdown</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border border-secondary-subtle h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="xsmall text-muted fw-bold">TAG BREADCRUMBS</span>
                        <span class="text-success"><i class="fa-solid fa-tags"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0"><?= (int)($stats['tags_count'] ?? 0) ?></h3>
                    <p class="xsmall text-muted mb-0 mt-1">Chủ đề phân cấp</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border border-secondary-subtle h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="xsmall text-muted fw-bold">PINS & LƯỢT THÍCH</span>
                        <span class="text-danger"><i class="fa-solid fa-thumbtack"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0"><?= (int)($stats['pins_count'] ?? 0) ?></h3>
                    <p class="xsmall text-muted mb-0 mt-1">Pinned StudyFlows</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Tags usage Chart -->
        <div class="col-md-6">
            <div class="card shadow-sm border border-secondary-subtle">
                <div class="card-header bg-body-tertiary fw-bold py-2.5 small">
                    <i class="fa-solid fa-chart-pie text-primary me-2"></i> Tần suất sử dụng Tags (Top 5)
                </div>
                <div class="card-body">
                    <div style="max-height: 250px; position: relative;" class="d-flex justify-content-center">
                        <canvas id="tagsPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly uploads activity -->
        <div class="col-md-6">
            <div class="card shadow-sm border border-secondary-subtle">
                <div class="card-header bg-body-tertiary fw-bold py-2.5 small">
                    <i class="fa-solid fa-chart-line text-success me-2"></i> Tần suất tải tài liệu hàng tuần
                </div>
                <div class="card-body">
                    <div style="max-height: 250px; position: relative;">
                        <canvas id="activityLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function profileDashboardData() {
        return {
            init() {
                this.$nextTick(() => {
                    this.renderCharts();
                });
            },
            
            renderCharts() {
                // 1. Tags Pie Chart
                const pieCtx = document.getElementById('tagsPieChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode($tagNames) ?>,
                        datasets: [{
                            data: <?= json_encode($tagCounts) ?>,
                            backgroundColor: <?= json_encode($tagColors) ?>,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                });

                // 2. Activity Line Chart
                const lineCtx = document.getElementById('activityLineChart').getContext('2d');
                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                        datasets: [{
                            label: 'Tài liệu tải lên',
                            data: [
                                <?= (int)($stats['weekly_activity']['Mon'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Tue'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Wed'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Thu'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Fri'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Sat'] ?? 0) ?>,
                                <?= (int)($stats['weekly_activity']['Sun'] ?? 0) ?>
                            ],
                            borderColor: '#238636',
                            backgroundColor: 'rgba(35, 134, 54, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        };
    }
</script>
