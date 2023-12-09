<!-- USER PAGE LOGIN OR REGISTER USE By Buffett -->

<?php
    // Get the session started to store usernames
    session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User page Login or Register</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <div class="container">
        <h1>Welcome Hooper's User</h1>
        <li class="user-info"><?php echo isset($_SESSION['username']) ? 'Welcome, ' . $_SESSION['username'] : 'No User Login'; ?></li>
        <div class="link-container">
            <a href="register.php" class="search-link">使用者註冊</a>
            <a href="login.php" class="search-link">使用者登入</a>
            <a href="match_search.php" class="search-link">參與約戰</a>
            <a href="court_search.php" class="search-link">場地搜索</a>
            <a href="court_display.php" class="search-link">場地查看</a>
            <a href="review.php" class="search-link">評價系統</a>
            <a href="logout.php" class="search-link">使用者登出</a>
        </div>
    </div>

    <div class="container">
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>

</html>

