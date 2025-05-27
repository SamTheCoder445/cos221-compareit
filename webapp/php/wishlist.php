<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$pageTitle = "Wishlist - CompareIt";


include 'header.php';
?>

<div class="container">
    <h1>Your Wishlist</h1>
    

   
    <div class="wishlist-items" id="wishlistItems">
        <!-- Wishlist items will be dynamically inserted here -->
    </div>
</div>

<?php

include 'footer.php';
?>

<script src="../js/wishlist.js"></script>
<script src="../js/loader.js"></script>
</body>
</html>