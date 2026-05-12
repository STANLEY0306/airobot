<?php
session_start();
include('includes/db_connect.php');
include('includes/header.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = trim($_POST['account']);
    $password = $_POST['password'];

    if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
        $search_field = 'email';
    } else {
        $search_field = 'username';
    }

    $query = "SELECT id, username, email, password_hash FROM users WHERE {$search_field} = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $account);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $email, $password_hash);
            $stmt->fetch();

            if ($password_hash && password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                header("Location: index.php");
                exit;
            } else {
                $message = "密碼錯誤";
            }
        } else {
            $message = ($search_field === 'email') ? "Email 不存在" : "帳號不存在";
        }
    } else {
        $message = "資料庫查詢錯誤";
    }
}
?>

<style>
.login-container {
    max-width: 500px;
    margin: 60px auto;
    padding: 0 20px;
}

.login-card {
    background: rgba(45, 20, 10, 0.92);
    border: 2px solid #b8860b;
    border-radius: 28px;
    padding: 45px 40px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
    backdrop-filter: blur(2px);
}

.login-card h2 {
    text-align: center;
    color: #e8c87a;
    font-size: 1.8rem;
    margin-bottom: 25px;
    letter-spacing: 4px;
    font-weight: normal;
}

.login-card label {
    display: block;
    color: #e8c87a;
    margin-bottom: 8px;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.login-card input {
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

.login-card input:focus {
    outline: none;
    border-color: #8b3a2a;
    box-shadow: 0 0 0 2px rgba(139, 58, 42, 0.2);
}

.login-btn {
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

.login-btn:hover {
    background: #9e4a38;
    transform: translateY(-2px);
}

.register-link {
    text-align: center;
    margin-top: 20px;
    color: #c4b896;
    font-size: 0.85rem;
}

.register-link a {
    color: #e8c87a;
    text-decoration: none;
    margin-left: 8px;
}

.register-link a:hover {
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
</style>

<div class="login-container">
    <div class="login-card">
        <h2>信眾登入</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>帳號 或 Email</label>
            <input type="text" name="account" required placeholder="請輸入帳號或Email">

            <label>密碼</label>
            <input type="password" name="password" required placeholder="請輸入密碼">

            <button type="submit" class="login-btn">登入</button>
        </form>

        <div class="register-link">
            尚未加入信眾？<a href="register.php">立即註冊</a>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>