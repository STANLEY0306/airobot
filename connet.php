<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lotNumber = $_POST['lot_number']; // 從 HTML 表單取得籤號

    // 這裡替換成你在 Colab 看到的 ngrok 網址
    $apiUrl = "https://overshort-nonprosperously-rupert.ngrok-free.dev"; 

    $data = array("lot_number" => (string)$lotNumber);
    $payload = json_encode($data);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if ($result['status'] === 'success') {
        $aiReply = $result['reply'];
    } else {
        $aiReply = "連線失敗，請檢查 API 狀態。";
    }
}
?>

<div class="result-box">
    <h3>導師的指引：</h3>
    <p><?php echo nl2br($aiReply); ?></p>
</div>