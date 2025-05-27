<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        sessionStorage.removeItem('auth');
        window.location.href = 'login.php';
    </script>
</head>
<body></body>
</html>
