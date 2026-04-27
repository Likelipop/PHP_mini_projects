<?php

// --- Init & Load ---
$products = require __DIR__ . '/../src/Data/productsData.php';
require __DIR__ . '/../src/Helpers/functions.php';

// --- Xử lý Logic Đặt Hàng (Form Submission) ---
$orderSuccessMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    // Lấy dữ liệu từ form (Trong thực tế bạn sẽ lưu dữ liệu này vào Database)
    $customerName = htmlspecialchars($_POST['fullname']);
    $customerPhone = htmlspecialchars($_POST['phone']);
    $customerAddress = htmlspecialchars($_POST['address']);
    $buyQuantity = (int)$_POST['quantity'];
    $productId = (int)$_POST['product_id'];

    // Gán thông báo thành công
    $orderSuccessMessage = "Order is successful, please check your phone.";
}

// --- Calculate Statistics ---
$totalItems = count($products);
$totalQuantity = getTotalQuantity($products);
$availableProducts = getAvailableProducts($products);
$availableCount = count($availableProducts);

// --- Lấy danh mục cho bộ lọc ---
$categories = array_unique(array_column($products, 'category'));

// --- Xử lý Logic Lọc ---
$selectedCategory = $_GET['category'] ?? '';
$selectedStock = $_GET['stock'] ?? '';

$filteredProducts = array_filter($products, function($item) use ($selectedCategory, $selectedStock) {
    $passCategory = empty($selectedCategory) || $item['category'] === $selectedCategory;
    $passStock = true;
    if ($selectedStock === 'in_stock') {
        $passStock = $item['quantity'] > 0;
    } elseif ($selectedStock === 'out_of_stock') {
        $passStock = $item['quantity'] <= 0;
    }
    return $passCategory && $passStock;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog | STATIONERY.APP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .product-card { transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    </style>
</head>
<body class="pb-5">

    <nav class="navbar navbar-dark bg-dark mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">STATIONERY<span class="text-warning">.APP</span></a>
        </div>
    </nav>

    <div class="container">
        
        <?php if (!empty($orderSuccessMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong>Successful!</strong> <?php echo $orderSuccessMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h3 class="mb-3">Inventory Dashboard</h3>
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card bg-primary text-white border-0 p-3 shadow-sm">
                    <p class="mb-1 opacity-75">Total Product Lines</p>
                    <h2 class="mb-0"><?php echo $totalItems; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white border-0 p-3 shadow-sm">
                    <p class="mb-1 opacity-75">Available Lines</p>
                    <h2 class="mb-0"><?php echo $availableCount; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white border-0 p-3 shadow-sm">
                    <p class="mb-1 opacity-75">Total Items in Stock</p>
                    <h2 class="mb-0"><?php echo $totalQuantity; ?></h2>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 p-3 bg-white">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="category" class="form-label fw-bold small text-muted">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selectedCategory === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="stock" class="form-label fw-bold small text-muted">Availability</label>
                    <select name="stock" id="stock" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="in_stock" <?php echo $selectedStock === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="out_of_stock" <?php echo $selectedStock === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Apply Filters</button>
                </div>
            </form>
        </div>

        <h3 class="mb-3">Product Catalog</h3>
        
        <?php if (empty($filteredProducts)): ?>
            <div class="alert alert-warning text-center" role="alert">
                No products found matching your criteria.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($filteredProducts as $item): ?>
                    <?php $status = getStockStatus($item['quantity']); ?>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($item['category']); ?></span>
                                    <span class="badge bg-<?php echo $status['color']; ?>"><?php echo $status['text']; ?></span>
                                </div>
                                <h5 class="card-title fw-bold text-truncate" title="<?php echo formatProductName($item['title']); ?>">
                                    <?php echo formatProductName($item['title']); ?>
                                </h5>
                                <h6 class="card-subtitle mb-4 text-muted">By <?php echo htmlspecialchars($item['brand']); ?></h6>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="fs-5 fw-bold text-primary"><?php echo formatPrice($item['price']); ?></span>
                                    <span class="text-secondary small">Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3">
                                <button class="btn btn-dark w-100 <?php echo $item['quantity'] <= 0 ? 'disabled' : ''; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#buyModal"
                                        data-product-id="<?php echo $item['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars(formatProductName($item['title'])); ?>"
                                        data-product-max="<?php echo $item['quantity']; ?>">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="buyModalLabel">Complete Your Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="buy">
                        <input type="hidden" name="product_id" id="modal-product-id" value="">
                        
                        <div class="mb-3">
                            <label for="fullname" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required placeholder="John Doe">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required placeholder="+1 234 567 890">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required placeholder="123 Main St..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-bold">Quantity</label>
                            <input type="number" class="form-control" id="modal-quantity" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Buy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var buyModal = document.getElementById('buyModal');
            buyModal.addEventListener('show.bs.modal', function (event) {
                // Lấy button vừa được click
                var button = event.relatedTarget;
                
                // Lấy thông tin sản phẩm từ các thuộc tính data-*
                var productId = button.getAttribute('data-product-id');
                var productName = button.getAttribute('data-product-name');
                var productMaxQty = button.getAttribute('data-product-max');
                
                // Cập nhật giao diện Modal
                var modalTitle = buyModal.querySelector('.modal-title');
                var modalProductId = buyModal.querySelector('#modal-product-id');
                var modalQuantityInput = buyModal.querySelector('#modal-quantity');
                
                modalTitle.textContent = 'Buy: ' + productName;
                modalProductId.value = productId;
                
                // Giới hạn số lượng mua không được vượt quá số lượng trong kho
                modalQuantityInput.setAttribute('max', productMaxQty);
                modalQuantityInput.value = 1; // Reset về 1 mỗi lần mở form
            });
        });
    </script>
</body>
</html>