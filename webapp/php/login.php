<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Login - CompareIt";
include 'header.php';
?>

<div class="login-container">
    <div class="login-card">
        <h1>Login</h1>
        <form id="login-form" class="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <p id="error-message" class="error-message"></p>
        <p class="signup-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</div>


<?php include 'footer.php'; ?>
<script src="../js/login.js"></script>