<!-- 場地搜索 By SamJay Lin -->
<!-- 約戰搜尋、參與 -->

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
    <title>Hooper Search Court</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Search Court - User</h1>
        <form action="court_search.php" method="post">
            <div>
                <label for="time">選擇時段：</label>
                <input type="time" name="time" id="time" value="08:00" required>
            </div>

            <div>
                <label for="duration">租借時長：</label>
                <select name="duration" id="duration" required>
                    <option value="1">1 小時</option>
                    <option value="2">2 小時</option>
                    <option value="3">3 小時</option>
                </select>
            </div>

            <div>
                <label for="date">選擇日期：</label>
                <input type="date" name="date" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div>
                <label for="capacity">需求人數：</label>
                <input type="number" name="requirednumberofplayers" id="requirednumberofplayers" value="5" required>
            </div>
            <div>
                <label for="price">租金可接受上限：</label>
                <input type="number" name="price" id="price" value="20000" step="100" required>
            </div>

            <div>
                <label for="capacity">總共人數：</label>
                <input type="number" name="capacity" id="capacity" value="10" step="1"required>
            </div>

            <input type="submit" value="Search">
        </form>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>


        <?php
            if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
                $timeSlot = $_POST['time'];
                $duration = $_POST['duration'];
                $date = $_POST['date'];
                $requirednumberofplayers = $_POST['requirednumberofplayers'];
                $maxPrice = $_POST['price'];
                $total = $_POST['capacity'];
                
                // 基於用戶輸入構建 SQL 查詢
                $query = "
                            SELECT c.CourtID, c.CourtName, c.Location, c.Price, c.Capacity
                            FROM COURT as c
                            WHERE c.Price <= :maxPrice 
                            AND c.Capacity >= :total
                            AND NOT EXISTS (
                                SELECT 1
                                FROM MATCH as m
                                WHERE m.CourtID = c.CourtID
                                AND m.Date = :date
                                AND m.Time >= :timeSlot
                                AND m.Time < (:timeSlot + INTERVAL '1 hour' * :duration)
                            )
                        ";
                try {
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':maxPrice', $maxPrice);
                    $stmt->bindParam(':total', $total);
                    $stmt->bindParam(':date', $date);
                    $stmt->bindParam(':timeSlot', $timeSlot);
                    $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // 顯示查詢結果
                    if ($results) {
                        echo "<h2>Search Results:</h2>";
                        echo "<table>";
                        echo "<tr><th>Date</th><th>Time</th><th>Court ID</th><th>Name</th><th>Location</th><th>Price</th><th>Capacity</th><th>Action</th></tr>";
                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($date ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($timeSlot ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($row['courtid']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['courtname']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['capacity']) . "</td>";

                            echo "<td>";
                            echo "<form method='post' action='create_match.php'>";
                            // TODO: add holderId(Because I can't try user login) helpppppp me!!!
                            // echo "<input type='hidden' name='holder_id' value='" . htmlspecialchars($_SESSION['$login_userid']) . "'>";
                            echo "<input type='hidden' name='court_id' value='" . htmlspecialchars($row['courtid']) . "'>";
                            echo "<input type='hidden' name='price' value='" . htmlspecialchars($row['price']) . "'>";
                            echo "<input type='hidden' name='total' value='" . htmlspecialchars($row['capacity']) . "'>";
                            echo "<input type='hidden' name='date' value='" . htmlspecialchars($date) . "'>";
                            echo "<input type='hidden' name='time' value='" . htmlspecialchars($timeSlot) . "'>";
                            echo "<input type='hidden' name='requirednumberofplayers' value='" . htmlspecialchars($requirednumberofplayers) . "'>";
                            echo "<button type='submit'>Create Match</button>";
                            echo "</form>";
                            echo "</td>";
                    
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p>No courts found matching your criteria.</p>";
                    }
                } catch (PDOException $e) {
                    echo "Error executing query: " . $e->getMessage();
                }
            }
            ?>
    </div>
</body>
</html>