<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stationery Express | Mini Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('https://images.unsplash.com/photo-1517842645767-c639042777db?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .card-product {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-product:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">STATIONERY<span class="text-warning">.APP</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">All Products</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold">Premium Office Supplies</h1>
            <p class="lead">From elegant pens to creative journals, find everything you need for your workspace.</p>
            <a href="products.php" class="btn btn-warning btn-lg px-5">Shop Now</a>
        </div>
    </header>

    <section class="container my-5">
        <h2 class="text-center mb-4">Featured Categories</h2>
        <div class="row g-4 text-center">
            
            <div class="col-md-4">
                <div class="card card-product p-4 bg-white">
                    <div class="display-5 mb-3 text-primary">🖋️</div>
                    <h4>Writing Tools</h4>
                    <p class="text-muted">High-quality pens and pencils.</p>
                    <a href="products.php?category=Writing+Instruments" class="btn btn-sm btn-outline-dark">Explore</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card card-product p-4 bg-white">
                    <div class="display-5 mb-3 text-success">📓</div>
                    <h4>Notebooks</h4>
                    <p class="text-muted">Keep your ideas in one place.</p>
                    <a href="products.php?category=Notebooks+%26+Paper" class="btn btn-sm btn-outline-dark">Explore</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card card-product p-4 bg-white">
                    <div class="display-5 mb-3 text-warning">🎨</div>
                    <h4>Arts & Crafts</h4>
                    <p class="text-muted">Unleash your creativity.</p>
                    <a href="products.php?category=Arts+%26+Crafts" class="btn btn-sm btn-outline-dark">Explore</a>
                </div>
            </div>

        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Mini Stationery Store. All rights reserved.</p>
            <div class="social-links">
                <small>Follow us on: Facebook | Instagram | Twitter</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>