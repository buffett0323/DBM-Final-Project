<?php
// Database connection
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

// Insert data into database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matchid = generateMatchId(); // Generate unique match ID

    // Retrieve form data
    $fee = $_POST['fee'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $holderid = $_POST['holderid'];
    $courtid = $_POST['courtid'];
    $requirednumberofplayers = $_POST['requirednumberofplayers'];

    try {
        // Insert form data into match table
        $sql = "INSERT INTO match (matchid, fee, date, time, holderid, courtid, requirednumberofplayers) VALUES (:matchid, :fee, :date, :time, :holderid, :courtid, :requirednumberofplayers)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':matchid', $matchid, PDO::PARAM_STR);
        $stmt->bindParam(':fee', $fee, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':holderid', $holderid, PDO::PARAM_STR);
        $stmt->bindParam(':courtid', $courtid, PDO::PARAM_STR);
        $stmt->bindParam(':requirednumberofplayers', $requirednumberofplayers, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $insertStatus = "Insert successful";
        } else {
            $insertStatus = "Insert failed";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to generate unique match ID
function generateMatchId() {
    $length = 20;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Match</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Add Match</h1>
        <form method="post">
            <label for="fee">Fee:</label>
            <input type="number" id="fee" name="fee" value="400" step="5" required><br><br>
            
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required><br><br>
            
            <label for="time">Time:</label>
            <input type="time" id="time" name="time" required><br><br>
            
            <label for="holderid">Holder ID:</label>
            <input type="text" id="holderid" name="holderid" required><br><br>
            
            <label for="courtid">Court ID:</label>
            <input type="text" id="courtid" name="courtid" required><br><br>
            
            <label for="requirednumberofplayers">Required Number of Players:</label>
            <input type="number" id="requirednumberofplayers" name="requirednumberofplayers" value="5" required><br><br>
            
            <input type="submit" value="Submit">
        </form>

        <?php if (isset($insertStatus)): ?>
            <p><?= $insertStatus ?></p>
        <?php endif; ?>
    
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button main" onclick="window.location.href='match_management.php';">Match Management</button>
    </div>
</body>
</html>
