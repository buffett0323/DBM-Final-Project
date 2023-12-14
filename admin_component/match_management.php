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

// Delete record if delete button is clicked
// Delete record if delete button is clicked
if (isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $pdo->beginTransaction();
    try {
        // Delete from positionshortage table
        $positionshortageSql = "DELETE FROM positionshortage WHERE matchid = :matchid";
        $positionshortageStmt = $pdo->prepare($positionshortageSql);
        $positionshortageStmt->bindParam(':matchid', $deleteId, PDO::PARAM_STR);
        $positionshortageStmt->execute();

        // Delete from participate table
        $participateSql = "DELETE FROM participate WHERE matchid = :matchid";
        $participateStmt = $pdo->prepare($participateSql);
        $participateStmt->bindParam(':matchid', $deleteId, PDO::PARAM_STR);
        $participateStmt->execute();

        // Delete from match table
        $matchSql = "DELETE FROM match WHERE matchid = :matchid";
        $matchStmt = $pdo->prepare($matchSql);
        $matchStmt->bindParam(':matchid', $deleteId, PDO::PARAM_STR);
        $matchStmt->execute();

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}


// Fetch match data based on holderid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['holderid'])) {
    $holderid = $_POST['holderid'];
    $sql = "SELECT * FROM match WHERE holderid = :holderid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':holderid', $holderid, PDO::PARAM_STR);
    $stmt->execute();
    $matchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Match Management</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Match Management</h1>
        <form method="post">
            <label for="holderid">Holder ID:</label>
            <input type="text" id="holderid" name="holderid" required>
            <button type="submit">Search</button>
        </form>

        <?php if (isset($matchData) && !empty($matchData)): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Match ID</th>
                        <th>Fee</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Holder ID</th>
                        <th>Court ID</th>
                        <th>Required Number of Players</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matchData as $match): ?>
                        <tr>
                            <td><?= $match['matchid'] ?></td>
                            <td><?= $match['fee'] ?></td>
                            <td><?= $match['date'] ?></td>
                            <td><?= $match['time'] ?></td>
                            <td><?= $match['holderid'] ?></td>
                            <td><?= $match['courtid'] ?></td>
                            <td><?= $match['requirednumberofplayers'] ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="delete_id" value="<?= $match['matchid'] ?>">
                                    <button class="delete-button" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($matchData) && empty($matchData)): ?>
            <p>No records found for the specified holder ID.</p>
        <?php endif; ?>

   
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
        <button class="button add_use" onclick="window.location.href='add_match.php';">Add Match</button>
        <!-- <a class="button main" href="add_match.php">Add Match</a> -->
    </div>
</body>
</html>
