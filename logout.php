<?php
session_start();
session_unset();
session_destroy();
include('includes/header.php');
?>

<style>
.logout-container {
    max-width: 500px;
    margin: 80px auto;
    padding: 0 20px;
}

.logout-card {
    background: rgba(45, 20, 10, 0.92);
    border: 2px solid #b8860b;
    border-radius: 28px;
    padding: 50px 40px;
    text-align: center;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
}

.logout-card h2 {
    color: #e8c87a;
    font-size: 1.8rem;
    margin-bottom: 15px;
    letter-spacing: 4px;
    font-weight: normal;
}

.logout-card p {
    color: #c4b896;
    margin-bottom: 30px;
    font-size: 0.95rem;
}

.logout-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.logout-btn {
    padding: 10px 28px;
    background: #8b3a2a;
    border: 1px solid #e8c87a;
    border-radius: 40px;
    color: #fef7e8;
    text-decoration: none;
    transition: all 0.3s;
    font-family: "標楷體", serif;
    letter-spacing: 2px;
}

.logout-btn:hover {
    background: #9e4a38;
    transform: translateY(-2px);
}
</style>

<div class="logout-container">
    <div class="logout-card">
        <h2>已成功登出</h2>
        <p>感謝使用線上抽籤平台，期待您再次登入。</p>
        <div class="logout-buttons">
            <a href="index.php" class="logout-btn">回首頁</a>
            <a href="login.php" class="logout-btn">重新登入</a>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>