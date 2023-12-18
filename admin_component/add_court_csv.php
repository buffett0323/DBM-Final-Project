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

if ( isset($_POST["submit"]) ) {
    if ( isset($_FILES["file"])) {
        //if there was an error uploading the file
         if ($_FILES["file"]["error"] > 0) {
             echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
 
         }
         else {
                $pdo->beginTransaction();
                try {
                if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $courtname = $data[0];
                        $location = $data[1];
                        $price = $data[2];
                        $capacity = $data[3];
                        $courtid = generateCourtId($pdo);
                        $sql = "INSERT INTO court (courtid, courtname, location, price, capacity) VALUES (:courtid, :courtname, :location, :price, :capacity)";
                        echo "Test1 <br />";  
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':courtid', $courtid, PDO::PARAM_STR);
                        $stmt->bindParam(':courtname', $courtname, PDO::PARAM_STR);
                        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
                        $stmt->bindParam(':price', $price, PDO::PARAM_INT);
                        $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);   
                        $stmt->execute();
                        }
                    }
                    $pdo->commit();
                    fclose($handle);
                }catch(\Throwable $e) { // use \Exception in PHP < 7.0
                    $pdo->rollBack();
                    throw $e;
                }
            }
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
        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">

        <tr>
        <td width="20%">Select file</td>
        <td width="80%"><input type="file" name="file" id="file" /></td>
        </tr>

        <tr>
        <td>Submit</td>
        <td><input type="submit" name="submit" /></td>
        </tr>

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
