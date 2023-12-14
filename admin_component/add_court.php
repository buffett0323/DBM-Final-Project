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

// Function to generate unique courtid
function generateCourtId($pdo) {
    try {
        // 尋找未被使用的最小 courtid
        $max_courtid = $pdo->query("SELECT COALESCE(MAX(SUBSTRING(courtid, 6)::int), 0) AS max_id FROM court")->fetchColumn();
        $next_id = null;

        for ($i = 1; $i <= $max_courtid + 1; $i++) {
            $check_courtid = 'Court' . str_pad($i, 3, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM court WHERE courtid = :check_courtid");
            $stmt->bindParam(':check_courtid', $check_courtid);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $next_id = $check_courtid;
                break;
            }
        }

        if ($next_id === null) {
            // 若找不到未使用的最小 courtid，則產生下一個 courtid
            $next_id = 'Court' . str_pad($max_courtid + 1, 3, '0', STR_PAD_LEFT);
        }

        return $next_id;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}




// Insert data into database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courtname = $_POST['courtname'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    $courtid = generateCourtId($pdo);
    
    $sql = "INSERT INTO court (courtid, courtname, location, price, capacity) VALUES (:courtid, :courtname, :location, :price, :capacity)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':courtid', $courtid, PDO::PARAM_STR);
    $stmt->bindParam(':courtname', $courtname, PDO::PARAM_STR);
    $stmt->bindParam(':location', $location, PDO::PARAM_STR);
    $stmt->bindParam(':price', $price, PDO::PARAM_INT);
    $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $insertStatus = "Insert successful";
    } else {
        $insertStatus = "Insert failed";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Court</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Add Court</h1>
        <form method="post">
            <label for="courtname">Court Name:</label>
            <input type="text" id="courtname" name="courtname" required><br><br>
            
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br><br>
            
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" value="4000" step="50" required><br><br>
            
            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" value="20" required><br><br>
            
            <input type="submit" value="Submit">
        </form>

        <?php if (isset($insertStatus)): ?>
            <p><?= $insertStatus ?></p>
        <?php endif; ?>
    
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="window.location.href='../admin_component/court_management.php';">Court Management</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>
</html>
