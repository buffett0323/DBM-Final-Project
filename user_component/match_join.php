<!-- Match 參與 （接續在 search_match 之後） By Buffett -->

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
    <title>Hooper Match Join</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Join Match</h1>
        <form action="match_join.php" method="post">
        <?php
            // Get the matchid sent
            if (isset($_GET['matchid'])) {
                $matchid = $_GET['matchid'];
                $sql = "
                    SELECT *
                    FROM positionshortage ps
                    Where ps.matchid = :matchid
                ";

                // Basic info processing
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':matchid', $matchid);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($results) {
                        echo "<table>";
                        echo "<tr><th>Match ID</th><th>Position Shortage</th><th>SIGN UP</th></tr>";

                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['matchid'] . "</td>";
                            echo "<td>" . $row['position'] . "</td>";
                            
                            // After clicking, removing the row in positionshortage and minus 1 in the database.
                            // echo "<td><button class='signup-button' onclick='callSignupFunction()'>Sign up</button></td>";
                            echo "<td>" . "<input type='submit' name='button' value='V'/>" . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "No position shortage found.";
                    }

                } catch (PDOException $e) {
                    echo "Error executing query: " . $e->getMessage();
                }
            } 

        ?>
        </form>

    <button class="button previous" onclick="history.back();">Previous Page</button>
    <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>
    </div>
</body>
</html>
