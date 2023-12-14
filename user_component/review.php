<?php
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

    $pdo = null; 
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
    }

    $userId = $_SESSION['login_userid']; 

    $sql = "SELECT u.userid, m.matchid, m.date, m.time, c.courtname
            FROM users as u
            JOIN participate as p on u.userid = p.userid
            JOIN match as m on p.matchid = m.matchid
            JOIN court as c on m.courtid = c.courtid
            WHERE u.userid = :userId
            ORDER BY m.date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hooper Review System</title>
    <style>
        .container {
            width: 80%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {background-color: #f2f2f2;}
        button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
    </head>
<body>
    <div class="container">
        <h1>Review System - User</h1>
        <?php
        if ($matches) {
            echo "<table>";
            echo "<tr><th>Match ID</th><th>Date</th><th>Time</th><th>Court Name</th><th>Action</th></tr>";
            foreach ($matches as $match) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($match['matchid']) . "</td>";
                echo "<td>" . htmlspecialchars($match['date']) . "</td>";
                echo "<td>" . htmlspecialchars($match['time']) . "</td>";
                echo "<td>" . htmlspecialchars($match['courtname']) . "</td>";
                // echo "<td><a href='rate_match.php?matchid=" . htmlspecialchars($match['matchid']) . "'>Rate this match</a></td>";

                echo "<td>";
                echo "<form action='rate_match.php' method='post'>";
                echo "<input type='hidden' name='userid' value='" . htmlspecialchars($userId) . "'>";
                echo "<input type='hidden' name='matchid' value='" . htmlspecialchars($match['matchid']) . "'>";
                echo "<button type='submit'>Rate this match</button>";
                echo "</form>";
                echo "</div>";
            }
            echo "</table>";
        } else {
            echo "<p>No matches found.</p>";
        }
        ?>
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>
    </div>
</body>
</html>
