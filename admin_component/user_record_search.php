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
        <h1>User Record Search</h1>

        <form action="user_record_search.php" method="post">
            使用者名稱: <input type="text" name="username" required>
            <button type="submit">Search</button>
        </form>

        <?php

        try {
            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get the username from the form
                $searchUsername = $_POST['username'];
                // Use a prepared statement to prevent SQL injection
                $stmt = $pdo->prepare('Select u.userid, u.username, u.level, m.matchid as holdingid, p.matchid as participatingid
                                    From users as u
                                    Left join match as m on m.holderid = u.userid
                                    Left join participate as p on p.userid = u.userid
                                    WHERE username = :username');
                $stmt->bindParam(':username', $searchUsername);
                $stmt->execute();

                // Fetch and display the results
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($result) {
                    echo '<h2>Search Results:</h2>';
                    echo '<h3>Holding Record:</h3>';
                    echo '<ul>';
                    foreach ($result as $row) {
                        echo '<li>Userid: ' . $row['userid'] . ', Username: ' . $row['username'] . ', Userlevel: ' . $row['level'] . ', Matchid: ' . $row['holdingid'] . ' </li>';
                    }
                    echo '<h3>Participating Record:</h3>';
                    foreach ($result as $row) {
                        echo '<li>Userid: ' . $row['userid'] . ', Username: ' . $row['username'] . ', Userlevel: ' . $row['level'] . ', Matchid: ' . $row['participatingid'] . ' </li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No records found for the specified username.</p>';
                }
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