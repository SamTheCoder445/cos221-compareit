<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - CompareIt</title>
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
            <li class="nav-item"><a href="admin-products.php" class="nav-link text-white"><i class="bi bi-box-seam me-2"></i>Products</a></li>
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
                <li><a class="dropdown-item" href="#">Logout</a></li>
            </ul>
            </div>
        </div>
        


        <div class="container mt-5">
        <h3>Search Product by ID</h3>
        <form id="searchForm" class="row g-3 mb-4">
            <div class="col-md-4">
            <input type="text" id="productIdSearch" class="form-control" placeholder="Enter Product ID" required>
            </div>
            <div class="col-md-2">
            <button type="submit" class="btn btn-primary" id="product-search-btn">Search</button>
            </div>
        </form>

        <!-- Product Display Section -->
        <div id="productInfo" class="card d-none">
            <div class="card-body">
            <h5 class="card-title" id="productName">Product Name</h5>
            <p class="card-text" id="productDetails">Details here...</p>
            <button class="btn btn-warning me-2" id="updateBtn">Update</button>
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
            </div>
        </div>
        </div>
    </div>

    <script src="/js/admin-products.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>