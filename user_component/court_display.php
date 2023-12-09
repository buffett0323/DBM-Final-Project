<!-- 場地查看 By Buffett -->
<!-- Bug: url cannot get pictures-->

<?php
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

    $sql = "
        SELECT ci.courtid, ci.url as url, ct.courtname as courtname
            From courtimage as ci
        Join court as ct 
            On ci.courtid = ct.courtid
    ";
    $stmt = $pdo->query($sql);
    // $stmt->execute();
    $courts = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Define url
    $selectedCourtUrl = "";
    if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedCourtId = $_POST['courtId'];
        foreach ($courts as $court) {
            if ($court['courtid'] == $selectedCourtId) {
                $selectedCourtUrl = $court['url'];
                break;
            }
        }
    }
?>




<!DOCTYPE html>
<html>
<head>
    <title>Court Display</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Court Display - User</h1>
        <form action="court_display.php" method="post">
            <label for="courtId"></label>
            搜尋場地照片： <select name="courtId" id="courtId">
                <?php 
                    $selectedCourt = $_POST['courtid'] ?? '';
                    try {
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($results as $row) {
                            $selected = ($row['courtid'] == $selectedCourt) ? 'selected' : '';
                            echo "<option value='" . $row['courtid'] . "' $selected>" . $row['courtname'] . "</option>";
                        }
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                ?>
            </select><br>
            <input type="submit" value="Load Image">
        </form>

        
        <?php if ($selectedCourtUrl): ?>
            <div>
                <img src="<?php echo $selectedCourtUrl; ?>" alt="Google Drive Image" style="max-width: 100%; height: auto;">
            </div>
        <?php endif; ?>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>

    </div>
</body>
</html>
