<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check both PHP session and client-side storage
$is_logged_in = isset($_SESSION['api_key']);
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CompareIt - Price Comparison (South Africa)') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/products.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/signup.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <script>
    // Check client-side authentication
    const authData = JSON.parse(sessionStorage.getItem('auth'));
    if (authData) {
        // Sync with PHP session if needed
        fetch('../php/sync_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ api_key: authData.api_key })
        });
    }
    </script>

    <header>
        <nav>
            <div class="logo">
                <img src="../img/logo.png" alt="CompareIt Logo" width="150">
            </div>
            <ul class="nav-links">
                <li><a href="products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">Products</a></li>
                <?php if($is_logged_in || isset($authData)): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?= $current_page === 'login.php' ? 'active' : '' ?>">Login</a></li>
                    <li><a href="signup.php" class="<?= $current_page === 'signup.php' ? 'active' : '' ?>">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>