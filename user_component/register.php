<!-- User Registration settings by Buffett -->

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

    function generateUniqueId($pdo) {
        $unique = false;
        $uniqueId = '';
    
        while (!$unique) {
            // Generate a random string of length 20
            $uniqueId = bin2hex(random_bytes(10)); 
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE userid = :userid");
            $stmt->execute(['userid' => $uniqueId]);
            if ($stmt->fetchColumn() == 0) {
                $unique = true; // Unique ID found
            } 
        }
        return $uniqueId;
    }
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>Users Registration</h1>
        <form action="register.php" method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            Birthday: <input type="date" name="birthday" required><br>
            Weight: <input type="number" name="weight" required><br>
            Height: <input type="number" name="height" required><br>
            
            
            <h3>User Position: </h3>
            <input type="checkbox" name="user_position[]" value="PF"> Power Forward<br>
            <input type="checkbox" name="user_position[]" value="SF"> Small Forward<br>
            <input type="checkbox" name="user_position[]" value="C"> Center<br>
            <input type="checkbox" name="user_position[]" value="SG"> Shooting Guard<br>
            <input type="checkbox" name="user_position[]" value="PG"> Point Guard<br>
            <input type="submit" value="Register">
        </form>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>


        <!-- Php Main function here -->
        <?php
            if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
                // Get the new ID
                $uniqueId = generateUniqueId($pdo);
                
                // Retrieve the data
                $username = $_POST['username'];
                $password = $_POST['password']; /* In the future, we can use password_hash($_POST['password'], PASSWORD_DEFAULT); */
                $birthday = $_POST['birthday'];
                $weight = $_POST['weight'];
                $height = $_POST['height'];
                $user_position = $_POST['user_position']; // Array

                // Basic info processing
                try {
                    $sql = "INSERT INTO users (userid, username, password, birthday, weight, height) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    
                    // Execute the statement with user data
                    if ($stmt->execute([$uniqueId, $username, $password, $birthday, $weight, $height])) {
                        echo "Registration successful with Unique ID: $uniqueId <br>";
                    } else {
                        echo "Error: Registration failed. <br>";
                    }
                } catch (PDOException $e) {
                    echo "Error executing query: " . $e->getMessage();
                }

                // Position info processing
                try {
                    // Insert each user position into user_positions table
                    foreach ($_POST['user_position'] as $position) {
                        $userPositionSql = "INSERT INTO position (userid, position) VALUES (?, ?)";
                        $userPositionStmt = $pdo->prepare($userPositionSql);
                        $userPositionStmt->execute([$uniqueId, $position]);
                    }
                    echo "Position registration successful with Unique ID: $uniqueId <br>";
                } catch (PDOException $e) {
                    echo "Error executing query: " . $e->getMessage();
                }

            }        
        ?>

    </div>
</body>
</html>

