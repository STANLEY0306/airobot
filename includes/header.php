<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>線上抽籤平台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <div class="site-title">線上抽籤平台</div>
        <a href="index.php">首頁</a>
        <a href="draw.php">抽籤</a>
        <a href="poem_library.php">籤詩查詢</a> <!-- 新增的連結 -->
        <a href="about.php">關於我們</a>
    </div>
    <div class="nav-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <span>歡迎, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn-nav">登出</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">登入</a>
            <a href="register.php" class="btn-nav">註冊</a>
        <?php endif; ?>
    </div>
</nav>