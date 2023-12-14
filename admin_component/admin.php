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
        <a href="add_court.php" class="search-link">新增球場</a>
        <a href="court_management.php" class="search-link">管理球場</a>
        <a href="add_match.php" class="search-link">新增約戰</a>
        <a href="match_management.php" class="search-link">管理約戰</a>
        <!-- Wait for other pages -->
    </div>

    <div class="container">
        <!-- Button to go back to the previous page and main page (index page) -->
        <button class="button previous" onclick="history.back();">Previous Page</button>
        <button class="button main" onclick="window.location.href='../index.php';">Main Page</button>
    </div>
</body>
</html>