<!-- User Login settings by Buffett -->

<?php
    session_start();
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
    <title>Login</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="container">
        <h1>User Login Page</h1>
        <form action="login.php" method="post">
            <div>
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username"><br>
            </div>
            <div>
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password"><br>
            </div>
            <input type="submit" value="Login">
        </form>

        <!-- Php code here -->
        <?php
        if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];
    
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
                $stmt->execute(['username' => $username, 'password' => $password]);
                
                if ($stmt->rowCount() > 0) {
                    $_SESSION['username'] = $username;
                    header('Location: user.php');   // Access to user profile
                } else {
                    echo "<h3>Invalid username or password!</h3><br>";
                }
            } catch (PDOException $e) {
                echo "Error executing query: " . $e->getMessage();
            }
        }
        ?>

        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button><br>

    </div>
    
</body>
</html>
