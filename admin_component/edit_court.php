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

// Retrieve the edit_id from the URL
if (!empty($_GET['edit_id'])) {
    $editId = $_GET['edit_id'];

    // Fetch court data based on the edit_id
    $sql = "SELECT * FROM court WHERE courtid = :courtid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':courtid', $editId, PDO::PARAM_STR);
    $stmt->execute();
    $courtData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courtData) {
        echo "Court not found!";
        exit;
    }
} else {
    echo "Invalid request!";
    exit;
}

// Update record if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courtname = $_POST['courtname'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

    // Check for duplicate courtname and location
    $sqlCheck = "SELECT * FROM court WHERE courtid != :courtid AND courtname = :courtname AND location = :location";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':courtid', $editId, PDO::PARAM_STR);
    $stmtCheck->bindParam(':courtname', $courtname, PDO::PARAM_STR);
    $stmtCheck->bindParam(':location', $location, PDO::PARAM_STR);
    $stmtCheck->execute();
    $duplicateData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($duplicateData) {
        echo "Error: Court with the same name and location already exists!";
    } else {
        try {
            $sql = "UPDATE court SET courtname = :courtname, location = :location, price = :price, capacity = :capacity WHERE courtid = :courtid";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':courtname', $courtname, PDO::PARAM_STR);
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_INT);
            $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
            $stmt->bindParam(':courtid', $editId, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $updateStatus = "Update successful";
                // Redirect to court management page after successful update
                header('Location: court_management.php');
                exit;
            } else {
                $updateStatus = "Update failed";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Court</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Edit Court</h1>
        <form method="post">
            <label for="courtname">Court Name:</label>
            <input type="text" id="courtname" name="courtname" value="<?= $courtData['courtname'] ?>" required><br><br>
            
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?= $courtData['location'] ?>" required><br><br>
            
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" value="<?= $courtData['price'] ?>" step="5" required><br><br>
            
            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" value="<?= $courtData['capacity'] ?>" required><br><br>
            
            <input type="submit" value="Update">
        </form>

        <?php if (isset($updateStatus)): ?>
            <p><?= $updateStatus ?></p>
        <?php endif; ?>
    
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="window.location.href='court_management.php';">Court Management</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>
</html>

