<?php
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
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['matchid'])) {
    $matchId = $_POST['matchid'];
    $userId = $_POST['userid'];
    $sql = "
        SELECT p.userid, u.username, u.weight, u.height
        FROM participate as p
        JOIN users as u on p.userid = u.userid
        WHERE p.matchid = :matchId
        AND p.userid != :currentUserId";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':matchId', $matchId);
    $stmt->bindParam(':currentUserId', $userId);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rate Match</title>
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
        <h1>Rate Match Participants</h1>
        <table>
            <tr>
                <th>User ID</th>
                <th>User Name</th>
                <th>weight</th>
                <th>height</th>
                <th>Action</th>
            </tr>
            <?php foreach ($participants as $participant) : ?>
                <tr>
                    <td><?= htmlspecialchars($participant['userid']); ?></td>
                    <td><?= htmlspecialchars($participant['username']); ?></td>
                    <td><?= htmlspecialchars(intval($participant['height'])); ?></td>
                    <td><?= htmlspecialchars(intval($participant['weight'])); ?></td>
                    <td>
                        <form action="rate_user.php" method="post">
                            <input type="hidden" name="matchid" value="<?= htmlspecialchars($matchId); ?>">
                            <input type="hidden" name="userid" value="<?= htmlspecialchars($userId); ?>">
                            <input type="hidden" name="reviewedid" value="<?= htmlspecialchars($participant['userid']); ?>">
                            <button type="submit">Rate User</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="container">
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>
    </div>
</body>
</html>
<?php
}
?>
