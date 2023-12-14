<?php
// Get the session started to store usernames
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin page</title>
<link rel="stylesheet" href="user.css">
</head>
<!-- ADMIN PAGE LOGIN USE -->
<!-- TODO: -->

<body>
    <div class="container">
        <h1>Welcome Hooper's Administrator</h1>
        <li class="admin-info">
            <?php echo isset($_SESSION['admin_name']) ? 'Welcome, ' . $_SESSION['admin_name'] : 'No Admin Login'; ?>
        </li>
        <div class="link-container">
            <a href="login.php" class="search-link">管理者登入</a>
            <a href="logout.php" class="search-link">管理者登出</a>
        </div>
        <!-- Wait for other pages -->
        <div class="link-container">
            <a href="add_court.php" class="search-link">新增球場</a>
            <a href="court_management.php" class="search-link">管理球場</a>
            <a href="add_match.php" class="search-link">新增約戰</a>
            <a href="match_management.php" class="search-link">管理約戰</a>
            <a href="user_record_search.php" class="search-link">查詢使用者活動紀錄</a>
            <a href="review_management.php" class="search-link">管理評價系統</a>
        </div>
    </div>

    <div class="container">
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>
</html>