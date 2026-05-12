<?php
session_start();
include('includes/db_connect.php');
include('includes/header.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";

    if (strlen($username) < 3 || strlen($password) < 3) {
        $message = "帳號與密碼至少需要 3 個字元";
    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email 格式錯誤";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $check = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "帳號或 Email 已被註冊";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $password_hash, $email);
                if ($stmt->execute()) {
                    $message = "註冊成功！請前往登入。";
                } else {
                    $message = "註冊失敗：" . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "資料庫錯誤";
            }
        }
        $check->close();
    }
}
?>

<style>
.register-container {
    max-width: 500px;
    margin: 60px auto;
    padding: 0 20px;
}

.register-card {
    background: rgba(45, 20, 10, 0.92);
    border: 2px solid #b8860b;
    border-radius: 28px;
    padding: 45px 40px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
    backdrop-filter: blur(2px);
}

.register-card h2 {
    text-align: center;
    color: #e8c87a;
    font-size: 1.8rem;
    margin-bottom: 25px;
    letter-spacing: 4px;
    font-weight: normal;
}

.register-card label {
    display: block;
    color: #e8c87a;
    margin-bottom: 8px;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.register-card input {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    background: #fef7e8;
    border: 1px solid #b8860b;
    border-radius: 12px;
    font-family: "標楷體", serif;
    font-size: 0.9rem;
    color: #2c1a10;
    transition: all 0.2s;
}

.register-card input:focus {
    outline: none;
    border-color: #8b3a2a;
    box-shadow: 0 0 0 2px rgba(139, 58, 42, 0.2);
}

.register-btn {
    width: 100%;
    padding: 12px;
    background: #8b3a2a;
    border: 1px solid #e8c87a;
    border-radius: 40px;
    color: #fef7e8;
    font-size: 1rem;
    font-family: "標楷體", serif;
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 3px;
}

.register-btn:hover {
    background: #9e4a38;
    transform: translateY(-2px);
}

.login-link {
    text-align: center;
    margin-top: 20px;
    color: #c4b896;
    font-size: 0.85rem;
}

.login-link a {
    color: #e8c87a;
    text-decoration: none;
    margin-left: 8px;
}

.login-link a:hover {
    text-decoration: underline;
}

.message {
    text-align: center;
    padding: 10px;
    margin-bottom: 20px;
    background: rgba(198, 40, 40, 0.3);
    border-left: 3px solid #c62828;
    color: #ffaaaa;
    border-radius: 8px;
    font-size: 0.9rem;
}

.message.success {
    background: rgba(107, 142, 107, 0.3);
    border-left-color: #6b8e6b;
    color: #b8d4b8;
}
</style>

<div class="register-container">
    <div class="register-card">
        <h2>信眾註冊</h2>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '成功') !== false ? 'success' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>帳號</label>
            <input type="text" name="username" required placeholder="請輸入帳號 (至少3字元)">

            <label>密碼</label>
            <input type="password" name="password" required placeholder="請輸入密碼 (至少3字元)">

            <label>Email（選填）</label>
            <input type="email" name="email" placeholder="example@domain.com">

            <button type="submit" class="register-btn">註冊</button>
        </form>

        <div class="login-link">
            已有帳號？<a href="login.php">立即登入</a>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>