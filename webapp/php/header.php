<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'CompareIt - Price Comparison (South Africa)') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/products.css">
  <link rel="stylesheet" href="../css/footer.css">
  <link rel="stylesheet" href="../css/signup.css">
  <link rel="stylesheet" href="../css/login.css">
  <link rel="stylesheet" href="../css/view.css">
    <link rel="stylesheet" href="../css/wishlist.css">
     
</head>
<body>
  <header>
    <nav>
      <div class="logo">
        <img src="../img/logo.png" alt="CompareIt Logo" width="150">
      </div>
      <ul class="nav-links" id="nav-links">
        <!-- fallback static nav in case JS is disabled -->
        <li><a href="products.php">Products</a></li>

        <li><a href="login.php">Login</a></li>
        <li><a href="signup.php">Sign Up</a></li>
      </ul>
    </nav>
  </header>

  <!-- Client-side nav replacement -->
  <script>
   document.addEventListener('DOMContentLoaded', () => {
  const nav = document.getElementById('nav-links');
  const currentPage = window.location.pathname.split('/').pop();

  const auth = JSON.parse(sessionStorage.getItem('auth'));

  if (auth) {
    nav.innerHTML = `
      <li><a href="products.php" class="${currentPage === 'products.php' ? 'active' : ''}">Products</a></li>
      <li><a href="wishlist.php" class="${currentPage === 'wishlist.php' ? 'active' : ''}">Wishlist</a></li>
      <li><a href="logout.php">Logout</a></li>
    `;
  } else {
    nav.innerHTML = `
      <li><a href="products.php" class="${currentPage === 'products.php' ? 'active' : ''}">Products</a></li>
      <li><a href="login.php" class="${currentPage === 'login.php' ? 'active' : ''}">Login</a></li>
      <li><a href="signup.php" class="${currentPage === 'signup.php' ? 'active' : ''}">Sign Up</a></li>
    `;
  }
});

  </script>

  <main>

<?php
$pagesWithLoader = ['products.php'];
if (in_array($current_page, $pagesWithLoader)) {
    include 'loader.php';
    echo '<link rel="stylesheet" href="../css/loader.css">';
}
?>
