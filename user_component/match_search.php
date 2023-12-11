<!-- Match 搜索 By Buffett -->

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
    <title>Hooper Search Match</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Search Match - User</h1>
        <form action="match_search.php" method="post">
            搜尋日期： <input type="date" name="date" value="<?= date('Y-m-d'); ?>" required><br>
            搜尋時間： <input type="time" name="time" id="time" value="08:00" required><br>
            <div>
                <label for="courtid"></label>
                搜尋場地： <select name="courtid" id="courtid">
                    <?php
                        $selectedCourt = $_POST['courtid'] ?? '';
                        try {
                            $stmt = $pdo->query("SELECT * FROM court");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($row['courtid'] == $selectedCourt) ? 'selected' : '';
                                echo "<option value='" . $row['courtid'] . "' $selected>" . $row['courtname'] . "</option>";
                            }
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                    ?>
                </select>
            </div>
            可接受金額上限： <input type="number" name="max_fee" required value="1000" step="5"><br>

            <input type="submit" value="Search">
        </form>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>


        <?php
            // Processing
            if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
                $date = $_POST['date'];
                $courtid = $_POST['courtid'];
                $fee = $_POST['max_fee'];
                $time = $_POST['time'];

                $sql = "
                    SELECT *
                    FROM match m
                    Where m.date = :date
                        AND m.courtid = :courtid
                        AND m.fee <= :fee
                        AND m.time >= :time
                        AND m.requirednumberofplayers > 0
                    Order By m.time
                ";

                // Basic info processing
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':date', $date);
                    $stmt->bindParam(':courtid', $courtid);
                    $stmt->bindParam(':fee', $fee);
                    $stmt->bindParam(':time', $time);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($results) {
                        echo "<table>";
                        echo "<tr><th>Match ID</th><th>Fee</th><th>Date</th><th>Time</th><th>Absence</th><th>Join Match</th></tr>";

                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['matchid'] . "</td>";
                            echo "<td>" . "$" . $row['fee'] . "</td>";
                            echo "<td>" . $row['date'] . "</td>";
                            echo "<td>" . $row['time'] . "</td>";
                            echo "<td>" . $row['requirednumberofplayers'] . "</td>";

                            // The button links to "match_join.php"
                            echo "<td><a href='match_join.php?matchid=" . $row['matchid'] . "'><button>Join Match</button></a></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "No court found.";
                    }

                } catch (PDOException $e) {
                    echo "Error executing query: " . $e->getMessage();
                }    
            }
        ?>
    </div>
</body>
</html>

