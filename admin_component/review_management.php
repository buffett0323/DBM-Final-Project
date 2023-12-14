<?php
// This page is only be accessible to logged-in users, add a check at the beginning.
session_start();

if (!isset($_SESSION['admin_name'])) {
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
    <title>match_search</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Review Record Search</h1>

        <form action="review_management.php" method="post">
            評論者ID: <input type="text" name="reviewerid" required>
            <button type="submit">Search</button>
        </form>

        <?php

        try {
            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get the username from the form
                $searchReviewerid = $_POST['reviewer'];
                echo $searchReviewerid;
                // Use a prepared statement to prevent SQL injection
                $stmt = $pdo->prepare('Delete from review
                                        WHERE reviewerid = :reviewerid');
                $stmt->bindParam(':reviewerid', $Reviewerid);
                $stmt->execute();
                echo 'Deleted obnoxious comments';

            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        } finally {
            // Close the database connection
            $pdo = null;
        }
        ?>

    </div>
    <div class="container">
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>

</html>