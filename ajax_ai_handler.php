<?php
session_start();
header('Content-Type: application/json');

// 檢查登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => '請先登入']);
    exit;
}

// 接收 JSON 格式的請求（新介面用 JSON）
$input = json_decode(file_get_contents('php://input'), true);

// 支援 POST 和 JSON 兩種格式
if ($input) {
    $user_question = $input['user_question'] ?? '';
    $lot_number = $input['lot_number'] ?? $_SESSION['last_lot_number'] ?? '';
    $god_name = $input['god_name'] ?? $_SESSION['last_god_name'] ?? '';
    $user_category = $input['user_category'] ?? $_SESSION['last_q_type'] ?? '綜合運勢';
} else {
    // 相容舊的 POST 格式
    $user_question = $_POST['user_question'] ?? '';
    $lot_number = $_SESSION['last_lot_number'] ?? '';
    $god_name = $_SESSION['last_god_name'] ?? '';
    $user_category = $_SESSION['last_q_type'] ?? '綜合運勢';
}

if (empty($user_question)) {
    echo json_encode(['success' => false, 'error' => '請輸入問題']);
    exit;
}

if (empty($lot_number)) {
    echo json_encode(['success' => false, 'error' => '尚未抽籤，請先抽籤']);
    exit;
}

// 您的 API 網址（請替換成實際的）
$apiUrl = 'https://premilitary-valery-unshocking.ngrok-free.dev/api/fortune';

$data = [
    "lot_number" => (string)$lot_number,
    "god_name" => $god_name,
    "user_category" => $user_category,
    "user_input" => $user_question
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'ngrok-skip-browser-warning: true'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response && $httpCode == 200) {
    $result = json_decode($response, true);
    
    // 嘗試多種可能的回應欄位名稱
    $reply = $result['reply'] ?? $result['response'] ?? $result['message'] ?? $result['answer'] ?? null;
    
    if ($reply) {
        echo json_encode([
            'success' => true,
            'reply' => $reply
        ]);
    } else {
        // 如果沒有標準欄位，回傳整個回應（除錯用）
        echo json_encode([
            'success' => true,
            'reply' => is_string($result) ? $result : '收到回應，但格式異常。請稍後再試。'
        ]);
    }
} else {
    $errorMsg = 'API 連線失敗';
    if ($httpCode) $errorMsg .= " (HTTP $httpCode)";
    if ($curlError) $errorMsg .= " - $curlError";
    
    echo json_encode([
        'success' => false,
        'error' => $errorMsg
    ]);
}
?>