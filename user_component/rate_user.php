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
        echo "資料庫連接失敗: " . $e->getMessage();
        exit;
    }

    $reviewSubmitted = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitReview'])) {
        $userId = $_POST['userid']; 
        $reviewedId = $_POST['reviewedid'];
        $comment = $_POST['comment'];
        $rating = $_POST['rating'];

        $sql = "INSERT INTO REVIEW (ReviewerID, RevieweeID, Comment, Rating) VALUES (:ReviewerID, :RevieweeID, :Comment, :Rating)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ReviewerID', $userId);
        $stmt->bindParam(':RevieweeID', $reviewedId);
        $stmt->bindParam(':Comment', $comment);
        $stmt->bindParam(':Rating', $rating);
        $stmt->execute();
        $reviewSubmitted = true;
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>評價用戶</title>
    <style>
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating label {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
        }
        .rating input[type="radio"] {
            display: none;
        }
        .rating input[type="radio"]:checked ~ label {
            color: gold;
        }
    </style>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <?php if ($reviewSubmitted): ?>
                <p>Successful update review to <?= htmlspecialchars($reviewedId); ?></p>
        <?php else: ?>
            <h1>評價用戶</h1>
            <form method="post" action="rate_user.php">
                <input type="hidden" name="userid" value="<?= htmlspecialchars($_POST['userid'] ?? ''); ?>">
                <input type="hidden" name="reviewedid" value="<?= htmlspecialchars($_POST['reviewedid'] ?? ''); ?>">
                <div class="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i; ?>" value="<?= $i; ?>" required>
                        <label for="star<?= $i; ?>">&#9733;</label>
                    <?php endfor; ?>
                </div>
                <label for="comment">評論（選填）:</label>
                <textarea name="comment" id="comment"></textarea>
                <input type="submit" name="submitReview" value="提交評價">
            </form>
        <?php endif; ?>
        
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>
    </div>
</body>
</html>

