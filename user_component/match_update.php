<?php
    // Database connection
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
    <title>Hooper Match Updating</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Updating Match</h1>
        
        <?php
            
            $matchid = $_POST['matchid'];
            $position = $_POST['position'];
    
            // Delete query
            $sql = "
                DELETE FROM positionshortage ps
                WHERE ps.matchid = :matchid
                    AND ps.position = :position
            ";
    
            // Basic info processing
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':matchid', $matchid);
                $stmt->bindParam(':position', $position);
                $stmt->execute();
            } catch (PDOException $e) {
                echo "Error executing query: " . $e->getMessage();
            }
    
            // Update query
            $sql = "
                UPDATE match
                SET requirednumberofplayers = requirednumberofplayers - 1
                WHERE matchid = :matchid;
            ";
    
            // Basic info processing
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':matchid', $matchid);
                $stmt->execute();
                
            } catch (PDOException $e) {
                echo "Error executing query: " . $e->getMessage();
            }
            echo "<h2>Delete & Update Database successfully<h2><br>";
        
        ?>

    <button class="button previous" onclick="history.back();">Previous Page</button>
    <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>
    </div>
</body>
</html>







