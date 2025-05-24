
<?php 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$pageTitle = "Products - CompareIt";

// Include header
include 'header.php';
?>

<div class="container">
    <!-- Search and filter controls -->
    <div class="search-filter-container">
        <div class="search-bar">
            <input type="text" placeholder="Search products...">
            <button type="submit">Search</button>
        </div>
        
        <div class="filter-sort-container">
            <!-- Price Range Filter -->
            <div class="filter">
                <label for="price-range">Price Range:</label>
                <select id="price-range">
                    <option value="all">All Prices</option>
                    <!-- Options populated by JavaScript -->
                </select>
            </div>

            <!-- Brand Filter -->
            <div class="filter">
                <label for="brand-filter">Brand:</label>
                <select id="brand-filter">
                    <option value="all">All Brands</option>
                    <!-- Options populated by JavaScript -->
                </select>
            </div>

            <!-- Category Filter -->
            <div class="filter">
                <label for="category">Category:</label>
                <select id="category">
                    <option value="all">All Categories</option>
                    <!-- Options populated by JavaScript -->
                </select>
            </div>

            <!-- Sort Options -->
            <div class="sort">
                <label for="sort-by">Sort By:</label>
                <select id="sort-by">
                    <option value="default">Default</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
            </div>
        </div>
    </div>

    <h1>Products</h1>
    
    <!-- Product Grid Container -->
    <div id="product-container">
        <div class="product-grid">
            <!-- Products will load here dynamically via JavaScript -->
        </div>
    </div>
</div>

<div class="pagination-controls"></div>

<?php include 'footer.php'; ?>
<script src="../js/products.js"></script>
</body>
</html>