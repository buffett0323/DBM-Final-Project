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
    <title>Review Management</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Review Record Search</h1>
        <form action="review_management.php" method="post">
            評論者ID: <input type="text" name="reviewerid" required>
            <button type="submit" name="search_submit">Search</button>
        </form>

        <?php

        try {
            $result = [];
            // Check if the form is submitted with the "Search" button
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
                // Get the reviewerid from the form
                $reviewerid = $_POST['reviewerid'];
                // Use a prepared statement to prevent SQL injection
                $stmt = $pdo->prepare('SELECT * 
            FROM review as r 
            WHERE r.reviewerid = :reviewerid
            ORDER BY reviewerid, revieweeid');
                $stmt->bindParam(':reviewerid', $reviewerid, PDO::FETCH_ASSOC);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>

        <?php
        // Delete record if delete button is clicked
        if (isset($_POST['delete_reviewerid']) && isset($_POST['delete_revieweeid'])) {
            $deleteReviewerId = $_POST['delete_reviewerid'];
            $deleteRevieweeId = $_POST['delete_revieweeid'];
            $sql = "DELETE FROM review 
                WHERE reviewerid = :reviewerid 
                AND revieweeid = :revieweeid";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':reviewerid', $deleteReviewerId, PDO::PARAM_STR);
            $stmt->bindParam(':revieweeid', $deleteRevieweeId, PDO::PARAM_STR);
            $stmt->execute();
            echo "<h3>Successfully Delete!<h3>";
        }
        ?>

        <table border="1">
            <thead>
                <tr>
                    <th>Reviewer ID</th>
                    <th>Reviewee ID</th>
                    <th>Comment</th>
                    <th>Rating</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $review): ?>
                    <tr>
                        <td>
                            <?= isset($review['reviewerid']) ? $review['reviewerid'] : 'N/A' ?>
                        </td>
                        <td>
                            <?= isset($review['revieweeid']) ? $review['revieweeid'] : 'N/A' ?>
                        </td>
                        <td>
                            <?= $review['comment'] ?? 'N/A' ?>
                        </td>
                        <td>
                            <?= $review['rating'] ?? 'N/A' ?>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="delete_reviewerid"
                                    value="<?= isset($review['reviewerid']) ? $review['reviewerid'] : '' ?>">
                                <input type="hidden" name="delete_revieweeid"
                                    value="<?= isset($review['revieweeid']) ? $review['revieweeid'] : '' ?>">
                                <button class="delete-button" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>

</html>