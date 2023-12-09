<!-- User logout. By Buffett -->

<?php
    session_start();
    session_unset();
    session_destroy();
    header('Location: user.php');
    exit;
?>
