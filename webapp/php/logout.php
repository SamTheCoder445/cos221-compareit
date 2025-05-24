<?php
session_start();
session_destroy();
?>
<script>
sessionStorage.removeItem('auth');
window.location.href = 'login.php';
</script>