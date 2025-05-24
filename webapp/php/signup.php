<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Sign Up - CompareIt";
include 'header.php';
?>

<div class="container">
    <h1>Create Your Account</h1>
    
    <form id="signupForm" class="auth-form">
        <div class="form-group">
            <label for="name">First Name</label>
            <input type="text" id="name" name="name" required>
            <span class="error-message" id="nameError"></span>
        </div>
        
        <div class="form-group">
            <label for="surname">Last Name</label>
            <input type="text" id="surname" name="surname" required>
            <span class="error-message" id="surnameError"></span>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <span class="error-message" id="emailError"></span>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="error-message" id="passwordError"></span>
            <div class="password-requirements">
                <p>Password must contain:</p>
                <ul>
                    <li id="length-req">At least 8 characters</li>
                    <li id="upper-req">One uppercase letter</li>
                    <li id="lower-req">One lowercase letter</li>
                    <li id="number-req">One digit</li>
                    <li id="special-req">One special character</li>
                </ul>
            </div>
        </div>
        
        <div class="form-group">
            <label for="userType">Account Type</label>
            <select id="userType" name="user_type" required>
                <option value="">Select account type</option>
                <option value="Customer">Customer</option>
                <option value="Admin">Admin</option>
            </select>
            <span class="error-message" id="typeError"></span>
        </div>
        
        <button type="submit" class="btn-primary">Create Account</button>
        
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<script src="../js/signup.js"></script>