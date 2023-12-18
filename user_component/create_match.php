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

    // pdo settings
    $pdo = null; 
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
        exit; 
    }

    // Function generating unique ID
    function generateUniqueId($pdo) {
        $unique = false;
        $uniqueId = '';
    
        while (!$unique) {
            // Generate a random string of length 20
            $uniqueId = bin2hex(random_bytes(10)); 
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM MATCH WHERE matchid = :matchid");
            $stmt->execute(['matchid' => $uniqueId]);
            if ($stmt->fetchColumn() == 0) {
                $unique = true; // Unique ID found
            } 
        }
        return $uniqueId;
    }
?>



<!DOCTYPE html>
<html>
<head>
    <title>Create Match</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Create Match Successfully!</h1>
        
        <!-- Php Main function here -->
        <?php
            // 檢查這是否是一個POST請求
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // 接收從表單發送的數據
                $courtId = $_POST['court_id'];
                $date = $_POST['date'];
                $time = $_POST['time'];
                $price = $_POST['price'];
                $total = $_POST['total'];
                $holderId = $_SESSION['login_userid']; // 這裡假設用戶ID已經以某種方式被傳遞，比如通過會話或表單隱藏字段
                $requirednumberofplayers = $_POST['requirednumberofplayers'];
                $fee = $total > 0 ? (int)($price / $total) : 0;
                // echo $courtId .', '. $date .', '. $time . ', '. $price . ', '. $total . ', '. $holderId . ', '. $requirednumberofplayers .', '. $fee;
                
                // 生成隨機的 MatchID
                $newMatchId = generateUniqueId($pdo);
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('LOCK TABLE match');
                $stmt->execute();
                // SQL command
                $sql = "INSERT INTO MATCH (MatchID, Fee, Date, Time, HolderID, CourtID, RequiredNumberofPlayers) 
                        VALUES (:MatchID, :Fee, :Date, :Time, :HolderID, :CourtID, :RequiredNumberofPlayers)";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':MatchID', $newMatchId);
                    $stmt->bindParam(':Fee', $fee);
                    $stmt->bindParam(':Date', $date);
                    $stmt->bindParam(':Time', $time);
                    $stmt->bindParam(':HolderID', $holderId);
                    $stmt->bindParam(':CourtID', $courtId);
                    $stmt->bindParam(':RequiredNumberofPlayers', $requirednumberofplayers);
                    $stmt->execute();

                    // Output the results
                    echo "Match Successfully Create";
                    echo "<table border='1'>"; // Start the table and set a border for visibility
                    echo "<tr><th colspan='2'>Match Details</th></tr>"; // Table header
                    echo "Match Successfully Create";
                    // Each row in the table represents a different piece of data
                    echo "<tr><td>Match ID</td><td>" . $newMatchId . "</td></tr>";
                    echo "<tr><td>Court ID</td><td>" . $courtId . "</td></tr>";
                    echo "<tr><td>Date</td><td>" . $date . "</td></tr>";
                    echo "<tr><td>Time</td><td>" . $time . "</td></tr>";
                    echo "<tr><td>Price</td><td>" . $price . "</td></tr>";
                    echo "<tr><td>Total</td><td>" . $total . "</td></tr>";
                    echo "<tr><td>Required Number of Players</td><td>" . $requirednumberofplayers . "</td></tr>";
                    echo "<tr><td>Fee(Each Person)</td><td>" . $fee . "</td></tr>";
                    echo "<tr><td>Match Holder</td><td>" . $_SESSION['username'] . "</td></tr>";
                    echo "</table>"; 
                    $pdo->commit();
                } catch (PDOException $e) {
                    #$pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
            } else {
                // 如果不是POST請求，則重定向回搜索頁面
                header('Location: search_court.php');
                exit;
            }
        ?>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>


        


    </div>
</body>
</html>

