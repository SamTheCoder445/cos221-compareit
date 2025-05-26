<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - PriceCompare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <div class="bg-dark text-white p-3" style="width: 300px;">
        <h4 class="text-white mb-4">CompareIt Dashboard</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-box-seam me-2"></i>Products</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-graph-up-arrow me-2"></i>Retailers</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-person me-2"></i>Users</a></li>
        </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                Admin <i class="bi bi-person-circle ms-1"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><a class="dropdown-item" href="#">Logout</a></li>
            </ul>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6>Total Products</h6>
                <h4 id="products-count">12,340</h4>
                <small class="text-muted" id="products-growth">+3.2% (in the last 30 days)</small>
                </div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6>Reviews</h6>
                <h4 id="reviews-count">1,265</h4>
                <small class="text-muted" id="reviews-growth">+12.4% (in the last 30 days)</small>
                </div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6>Users</h6>
                <h4 id="users-count">8,721</h4>
                <small class="text-muted" id="users-growth">+1.1% (in the last 30 days)</small>
                </div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6>Stores Tracked</h6>
                <h4 id="retailers-count">85</h4>
                <small class="text-muted" id="retailers-growth">0% (in the last 30 days)</small>
                </div>
            </div>
            </div>
        </div>

        <!-- Charts and Tables -->
        <div class="row">
            <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6 class="card-title">Price Trend (Last 30 days)</h6>
                <canvas id="priceChart" height="150" width="400"></canvas>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                <h6 class="card-title">Wishlist Saves (Last 30 days)</h6>
                <canvas id="wishlistChart" height="150" width="400"></canvas>
                </div>
            </div>
            </div>
            <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                <h6 class="card-title">Top Categories</h6>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Speakers <span class="badge bg-success">1243 Saves</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Tablets <span class="badge bg-success">932 Saves</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Headphones <span class="badge bg-success">789 Saves</span>
                    </li>
                </ul>
                </div>
            </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="card shadow-sm">
            <div class="card-body">
            <h6 class="card-title mb-3">Recently Added Products</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Store</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>iPhone 15 Pro</td>
                    <td>Phones</td>
                    <td>R23,299</td>
                    <td>Apple</td>
                </tr>
                <tr>
                    <td>JBL Xtreme 4</td>
                    <td>Speakers</td>
                    <td>R6,499</td>
                    <td>JBL</td>
                </tr>
                <tr>
                    <td>85" Crystal UHD DUE800 4K Tizen OS Smart TV</td>
                    <td>TV</td>
                    <td>R21,999</td>
                    <td>Samsung</td>
                </tr>
                </tbody>
            </table>
            </div>
        </div>

        </div>
    </div>
    <script type="module" src="/js/dashboard.js"></script>
    <script type="module" src="/js/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
