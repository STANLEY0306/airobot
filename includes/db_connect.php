<?php
// 資料庫參數
$host = 'localhost';    // XAMPP 預設是 localhost
$db   = 'fortune_draw'; // 你建立的資料庫名稱
$user = 'root';         // XAMPP 預設帳號
$pass = '';             // XAMPP 預設密碼是空字串

// 建立連線
$mysqli = new mysqli($host, $user, $pass, $db);

// 檢查連線是否成功
if ($mysqli->connect_errno) {
    die("資料庫連線失敗: " . $mysqli->connect_error);
}
?>
