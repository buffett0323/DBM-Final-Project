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
if (isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $sql = "DELETE FROM court WHERE courtid = :courtid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':courtid', $deleteId, PDO::PARAM_STR);
    $stmt->execute();
}

// Fetch court data
$sql = "SELECT * FROM court Order by courtid";
$stmt = $pdo->query($sql);
$courtData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Court Management</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Updating Court</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>courtid</th>
                    <th>courtname</th>
                    <th>location</th>
                    <th>price</th>
                    <th>capacity</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courtData as $court): ?>
                    <tr>
                        <td><?= $court['courtid'] ?></td>
                        <td><?= $court['courtname'] ?></td>
                        <td><?= $court['location'] ?></td>
                        <td><?= $court['price'] ?></td>
                        <td><?= $court['capacity'] ?></td>
                        <td>
                            <!-- 新增修改按鈕 -->
                            <form method="get" action="edit_court.php">
                                <input type="hidden" name="edit_id" value="<?= $court['courtid'] ?>">
                                <button class="pencil-button" type="submit">Edit</button>
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="delete_id" value="<?= $court['courtid'] ?>">
                                <button class="delete-button" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
        <button class="button add_use" onclick="window.location.href='add_court.php';">Add Court</button>
        <!-- <a class="button add" href="add_court.php">Add Court</a> -->
    </div>
</body>
</html>
