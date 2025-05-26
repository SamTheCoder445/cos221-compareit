
<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$pageTitle = "Product Details - Compareit";

// Include header
include 'header.php';
?>


<div id="product-container">
    <h1 id="product-title"></h1>
    <p id="product-rating"></p>
    <p id="product-brand-stock"></p>

    <div id="image-carousel">
        <button id="prev-img">←</button>
        <img id="carousel-img" src="" alt="Product Image">
        <button id="next-img">→</button>
    </div>


    
<p id="product-description"></p>

    <table id="product-details"></table>
    


    <h3>💰 Available Prices</h3>
    <table id="price-table">
        <thead>
            <tr><th>Retailer</th><th>Price</th><th>Action</th></tr>
        </thead>
        <tbody></tbody>
    </table>

    <h3>📝 Customer Reviews</h3>
    <div id="review-carousel">
        <button id="prev-review">←</button>
        <div id="review-content"></div>
        <button id="next-review">→</button>
    </div>

    <div id="add-review-section">
        <h4>Leave a Review</h4>
        <select id="review-rating">
            <option value="1">⭐</option>
            <option value="2">⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
        </select>
        <textarea id="review-comment" placeholder="Write your review here..."></textarea>
        <button id="submit-review">Submit Review</button>
    </div>
</div>

<?php include 'footer.php'; ?>


<script src="../js/view.js"></script>

</body>
</html>















