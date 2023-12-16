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
    <title>User Record</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Inquiring User Record</h1>

        <form action="user_record_search.php" method="post">
            使用者ID: <input type="text" name="userid" required>
            <button type="submit">Search</button>
        </form>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
        
        <?php
        try {
            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get the username from the form
                $userid = $_POST['userid'];
                // Use a prepared statement to prevent SQL injection
                $stmt = $pdo->prepare('SELECT u.userid, u.username, u.level, m.matchid as holding_match, p.matchid as participate_match
                    FROM users as u
                    LEFT JOIN match as m on m.holderid = u.userid
                    LEFT JOIN participate as p on p.userid = u.userid
                    WHERE u.userid = :userid');
                $stmt->bindParam(':userid', $userid);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Display the result
                if ($result) {
                    // echo "<h2>User Record</h2>";
                    echo "<table>";
                    echo "<tr><th>User ID</th><th>User Name</th><th>User Level</th><th>Holding Match</th><th>Participate Match</th></tr>";
                    foreach ($result as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['userid']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['level']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['holding_match']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['participate_match']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<p>No records found matching your input ID.</p>";
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>
</body>

</html>