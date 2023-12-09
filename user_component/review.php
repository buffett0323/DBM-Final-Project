<?php
    // This page is only be accessible to logged-in users, add a check at the beginning.
    session_start();

    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
    
    $host = 'localhost';
    $port = 5432; 
    $dbname = 'hooper';
    $user = 'postgres'; 
    $password = trim(file_get_contents('../db_password.txt'));

    // pdo settings
    $pdo = null; 
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
    }
?>



<!DOCTYPE html>
<html>
<head>
    <title>Hooper Review</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Review - User</h1>
                     
    </div>
</body>
</html>

