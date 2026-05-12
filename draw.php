<?php 
session_start();
include('includes/db_connect.php'); 
include('includes/header.php'); 

// 1. 變數初始化與權限檢查
$user_id = $_SESSION['user_id'] ?? 0;
$show_result = false;

$god_configs = [
    'general' => ['name'=>'綜合運勢', 'img'=>'god1.png', 'min'=>1, 'max'=>60, 'god_title'=>'天上聖母', 'prayer'=>'護國庇民，海上守護'],
    'love' => ['name'=>'感情婚姻', 'img'=>'god2.png', 'min'=>61, 'max'=>160, 'god_title'=>'月下老人', 'prayer'=>'千里姻緣一線牽'],
    'career' => ['name'=>'事業工作', 'img'=>'god3.png', 'min'=>161, 'max'=>220, 'god_title'=>'財神爺', 'prayer'=>'招財進寶，財源廣進']
];

function numberToChinese($num) {
    $chiNum = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
    if ($num <= 10) return ($num == 10) ? "十" : $chiNum[$num];
    if ($num < 20) return "十" . ($num % 10 == 0 ? "" : $chiNum[$num % 10]);
    $res = "";
    if ($num >= 100) {
        $res .= $chiNum[floor($num / 100)] . "百";
        $num %= 100;
        if ($num < 10 && $num > 0) $res .= "零";
    }
    if ($num >= 10) {
        $res .= $chiNum[floor($num / 10)] . "十";
        $num %= 10;
    }
    if ($num > 0) $res .= $chiNum[$num];
    return $res;
}

// 重置功能
if (isset($_GET['reset'])) {
    unset(
        $_SESSION['last_lot_number'], $_SESSION['last_lot_text'],
        $_SESSION['last_interpretation'], $_SESSION['last_fortune'],
        $_SESSION['last_q_type'], $_SESSION['last_god_name']
    );
    header("Location: draw.php"); 
    exit;
}

// 2. 抽籤邏輯
if (isset($_POST['draw'])) {
    $q_type = $_POST['question_type'] ?? 'general';
    $config = $god_configs[$q_type];
    
    $sql = "SELECT * FROM lottery_poems WHERE number BETWEEN ? AND ? ORDER BY RAND() LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $config['min'], $config['max']);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res && $row = $res->fetch_assoc()) {
        $_SESSION['last_lot_number'] = $row['number'];
        $_SESSION['last_lot_text'] = $row['poem_text'];
        $_SESSION['last_interpretation'] = $row['interpretation'];
        $_SESSION['last_fortune'] = $row['fortune_level'];
        $_SESSION['last_q_type'] = $q_type;
        $_SESSION['last_god_name'] = $config['god_title'];
        
        $insert_stmt = $mysqli->prepare("INSERT INTO lottery_history (user_id, god_name, number, fortune_level, poem_text, interpretation, question_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isissss", $user_id, $_SESSION['last_god_name'], $_SESSION['last_lot_number'], $_SESSION['last_fortune'], $_SESSION['last_lot_text'], $_SESSION['last_interpretation'], $_SESSION['last_q_type']);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
}

if (isset($_SESSION['last_lot_number'])) {
    $show_result = true;
}

$raw_num = $_SESSION['last_lot_number'] ?? 0;
$q_type = $_SESSION['last_q_type'] ?? 'general';
$display_num = ($raw_num > 0) ? $raw_num - ($god_configs[$q_type]['min'] - 1) : '';
$current_god_title = $_SESSION['last_god_name'] ?? '天上聖母';
$current_fortune = $_SESSION['last_fortune'] ?? '';

$fortune_colors = [
    '大吉' => '#d4a13e',
    '上吉' => '#c4727a',
    '中吉' => '#6b8e6b',
    '小吉' => '#8b6b4a',
    '中平' => '#5a6e6e',
    '平' => '#6e5a5a',
    '下下' => '#8b5a5a'
];
$fortune_color = $fortune_colors[$current_fortune] ?? '#8b6b4a';
?>

<style>
/* ===== 組員風格 - 溫暖寺廟版 ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #947455;
    background-image: radial-gradient(circle at 25% 30%, rgba(201, 160, 61, 0.12) 1%, transparent 1.5%);
    background-size: 40px 40px;
    min-height: 100vh;
    font-family: "標楷體", "新細明體", serif;
}

.draw-page-wrapper { max-width: 1500px; margin: 40px auto; padding: 0 25px; }

/* ===== 抽籤前選擇區 ===== */
.draw-select-section {
    max-width: 1100px;
    margin: 50px auto;
    padding: 0 20px;
}

.draw-select-card {
    background: #d4a87a;
    background-image: linear-gradient(0deg, rgba(160, 100, 50, 0.08) 1px, transparent 1px);
    background-size: 100% 4px;
    border: 1px solid #e8c87a;
    border-radius: 28px;
    padding: 45px 40px;
    text-align: center;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
    position: relative;
}

.draw-select-card::before {
    content: "";
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    border: 1px solid rgba(232, 200, 122, 0.5);
    border-radius: 20px;
    pointer-events: none;
}

.draw-select-card h1 {
    font-size: 2rem;
    color: #3a2212;
    margin-bottom: 12px;
    letter-spacing: 8px;
    font-weight: normal;
    text-shadow: 1px 1px 0 rgba(255,255,255,0.2);
}

.draw-select-card > p {
    color: #4a2a18;
    margin-bottom: 40px;
    font-size: 0.9rem;
    letter-spacing: 2px;
    font-weight: 500;
}

.god-selector-container {
    display: flex;
    justify-content: center;
    gap: 45px;
    margin: 30px 0 40px;
    flex-wrap: wrap;
}

.god-option {
    cursor: pointer;
    width: 210px;
    text-align: center;
    transition: all 0.3s ease;
}

.god-option:hover {
    transform: translateY(-8px);
}

.god-img-wrapper {
    width: 100%;
    height: 240px;
    background: #f5e6c8;
    border: 5px solid #8b5a2b;
    border-radius: 18px 18px 12px 12px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 0 0 2px #e8c87a, 0 10px 20px rgba(0,0,0,0.25);
}

.god-img-wrapper::before {
    content: "▲";
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 18px;
    color: #8b5a2b;
    text-shadow: 0 1px 0 #e8c87a;
}

.god-img-wrapper::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 45px;
    background: linear-gradient(to top, rgba(139, 60, 42, 0.3), transparent);
    pointer-events: none;
}

.god-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: contrast(1.03) saturate(0.92);
}

.god-option.selected .god-img-wrapper {
    border-color: #e8c87a;
    box-shadow: 0 0 0 3px #e8c87a, 0 0 20px rgba(232, 200, 122, 0.5), 0 10px 20px rgba(0,0,0,0.3);
}

.god-option.selected .god-img-wrapper::before {
    color: #e8c87a;
    text-shadow: 0 0 6px #e8c87a;
}

.god-option span {
    display: block;
    margin-top: 16px;
    color: #3a2212;
    font-weight: bold;
    font-size: 1.15rem;
    letter-spacing: 2px;
}

.god-option small {
    display: block;
    color: #5a3a22;
    font-size: 0.7rem;
    margin-top: 5px;
    letter-spacing: 1px;
}

.btn-ritual {
    padding: 12px 35px;
    border-radius: 50px;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    letter-spacing: 3px;
    font-family: "標楷體", serif;
}

.btn-primary {
    background: #8b3a2a;
    color: #f0e6d0;
    border: 1px solid #e8c87a;
}

.btn-primary:hover {
    background: #9e4a38;
    transform: translateY(-2px);
}

#divination-area {
    margin-top: 35px;
}

#shake-container {
    background: rgba(58, 34, 18, 0.45);
    border-radius: 24px;
    padding: 35px;
    border: 1px solid #e8c87a;
}

#statusTitle {
    color: #3a2212;
    margin-bottom: 20px;
    font-size: 1.2rem;
    font-weight: bold;
    letter-spacing: 2px;
}

#divination-result {
    font-size: 1.6rem;
    font-weight: bold;
    color: #3a2212;
    margin: 20px 0;
}

/* ===== 抽籤結果區 - 組員風格左右對齊 ===== */
.result-container {
    width: 100%;
    margin: 0 auto;
}

.result-layout {
    display: flex;
    gap: 30px;
    align-items: stretch;
}

/* 左側籤詩區 - 組員卷軸風格 */
.lottery-main-scroll {
    flex: 6.5;
    background: url('images/scripture-texture.jpg') repeat,
                linear-gradient(135deg, rgba(232,216,181,0.95), rgba(245,232,200,0.9));
    background-size: cover;
    background-blend-mode: overlay;
    border: 2px solid #b8860b;
    border-left: 12px solid #8b0000;
    border-right: 12px solid #8b0000;
    padding: 40px 30px;
    position: relative;
    box-shadow: 0 18px 30px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    height: 700px;
    overflow-y: auto;
}

.lottery-main-scroll::-webkit-scrollbar {
    width: 5px;
}
.lottery-main-scroll::-webkit-scrollbar-track {
    background: rgba(139, 69, 19, 0.1);
    border-radius: 3px;
}
.lottery-main-scroll::-webkit-scrollbar-thumb {
    background: #b8860b;
    border-radius: 3px;
}

.lottery-main-scroll::before {
    content: "卍";
    position: absolute;
    top: 15px;
    right: 25px;
    color: rgba(183,134,11,0.3);
    font-size: 1.5rem;
    font-weight: bold;
}

.lottery-main-scroll::after {
    content: "卍";
    position: absolute;
    bottom: 15px;
    left: 25px;
    color: rgba(183,134,11,0.3);
    font-size: 1.5rem;
    font-weight: bold;
    transform: rotate(180deg);
}

.poem-header-top {
    font-weight: bold;
    color: #8b0000;
    letter-spacing: 2px;
    text-align: center;
    font-size: 1.3rem;
    margin-bottom: 10px;
    flex-shrink: 0;
}

.fortune-title {
    font-size: 2rem;
    color: #8b0000;
    margin: 10px 0;
    text-align: center;
    flex-shrink: 0;
}

.poem-frame {
    border: 6px double #8b0000;
    padding: 30px 25px;
    margin: 20px auto;
    background: rgba(255,255,255,0.2);
    width: 95%;
    min-height: 200px;
    max-height: 220px;
    overflow-y: auto;
    flex-shrink: 0;
}

.poem-frame::-webkit-scrollbar {
    width: 4px;
}
.poem-frame::-webkit-scrollbar-track {
    background: rgba(139, 69, 19, 0.1);
}
.poem-frame::-webkit-scrollbar-thumb {
    background: #b8860b;
}

.poem-text-content {
    font-size: 1.5rem;
    font-family: "標楷體", serif;
    font-weight: 900;
    color: #2c1810;
    line-height: 1.8;
    text-align: center;
}

.interpretation-card {
    background: url('images/scripture-texture.jpg') repeat;
    background-blend-mode: overlay;
    background-color: rgba(255,255,255,0.1);
    border: 2px solid #b8860b;
    border-left: 8px solid #8b0000;
    padding: 20px;
    margin: 15px 0;
    text-align: left;
    height: 200px;
    overflow-y: auto;
    flex-shrink: 0;
}

.interpretation-card::-webkit-scrollbar {
    width: 4px;
}
.interpretation-card::-webkit-scrollbar-track {
    background: rgba(139, 69, 19, 0.1);
}
.interpretation-card::-webkit-scrollbar-thumb {
    background: #b8860b;
}

.interpretation-card h3 {
    color: #8b0000;
    border-bottom: 1px solid #b8860b;
    font-size: 1.1rem;
    margin-bottom: 8px;
    padding-bottom: 5px;
}

.interpretation-card p {
    font-weight: bold;
    line-height: 1.7;
    color: #2c1810;
    font-size: 0.9rem;
}

.action-buttons-bottom {
    text-align: center;
    margin-top: auto;
    padding-top: 20px;
    flex-shrink: 0;
}

.action-buttons-bottom .btn-ritual {
    margin: 0 5px;
    padding: 8px 20px;
    font-size: 0.9rem;
}

.btn-reset {
    background: #5a3a28;
    color: #e8d8b0;
    border: 1px solid #b8860b;
}

.btn-history {
    background: #3a2518;
    color: #e8d8b0;
    border: 1px solid #b8860b;
}

/* ===== 右側 AI 聊天面板 - 固定高度對齊 ===== */
.chat-panel {
    flex: 3.5;
    background: rgba(45, 20, 10, 0.92);
    border: 2px solid #b8860b;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    height: 700px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #8b0000, #5c3317);
    border-bottom: 2px solid #b8860b;
    flex-shrink: 0;
}

.chat-header-icon { font-size: 24px; }

.chat-title {
    font-size: 1rem;
    font-weight: bold;
    color: #b8860b;
    letter-spacing: 2px;
}

.chat-subtitle {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.65);
    margin-top: 3px;
}

/* 預設問題按鈕區 */
.quick-questions {
    padding: 10px 15px;
    background: rgba(0,0,0,0.3);
    border-bottom: 1px solid rgba(183,134,11,0.3);
    flex-shrink: 0;
}

.quick-title {
    font-size: 0.7rem;
    color: #b8860b;
    margin-bottom: 6px;
    letter-spacing: 1px;
}

.quick-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.quick-btn {
    background: rgba(183,134,11,0.15);
    border: 1px solid #b8860b;
    padding: 4px 10px;
    border-radius: 20px;
    color: #f0e6d0;
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-btn:hover {
    background: #b8860b;
    color: #2c1a10;
}

/* 訊息區 */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-track { background: rgba(183,134,11,0.1); }
.chat-messages::-webkit-scrollbar-thumb { background: #b8860b; border-radius: 3px; }

.bubble-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    animation: fadeInUp 0.3s ease;
}

.ai-row   { flex-direction: row; }
.user-row { flex-direction: row-reverse; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
    border: 1px solid #b8860b;
}

.ai-avatar   { background: rgba(93,64,55,0.8); }
.user-avatar { background: rgba(139,0,0,0.6); }

.bubble {
    max-width: 78%;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    line-height: 1.55;
}

.ai-bubble {
    background: rgba(183,134,11,0.12);
    border: 1px solid rgba(183,134,11,0.35);
    color: #f5e6c8;
    border-bottom-left-radius: 3px;
}

.user-bubble {
    background: linear-gradient(135deg, #8b0000, #a00000);
    color: #fff;
    border-bottom-right-radius: 3px;
    box-shadow: 0 2px 6px rgba(139,0,0,0.3);
}

/* 打字動畫 */
.typing-bubble {
    display: flex; align-items: center; gap: 4px;
    padding: 10px 14px;
}

.typing-bubble span {
    display: block; width: 6px; height: 6px; border-radius: 50%;
    background: #b8860b;
    animation: dotBounce 1.2s infinite ease-in-out;
}

.typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
.typing-bubble span:nth-child(3) { animation-delay: 0.4s; }

@keyframes dotBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-5px); opacity: 1; }
}

/* 輸入區 */
.chat-input-area {
    display: flex;
    gap: 8px;
    padding: 12px 15px;
    background: rgba(20, 8, 3, 0.7);
    border-top: 1px solid rgba(183,134,11,0.3);
    flex-shrink: 0;
}

.chat-input-area textarea {
    flex: 1;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(183,134,11,0.4);
    border-radius: 10px;
    color: #f5e6c8;
    font-size: 0.85rem;
    padding: 8px 12px;
    resize: none;
    outline: none;
    font-family: "標楷體", serif;
}

.chat-input-area textarea:focus { border-color: #b8860b; }
.chat-input-area textarea::placeholder { color: rgba(255,255,255,0.3); }

.send-btn {
    width: 38px; height: 38px;
    align-self: flex-end;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #8b0000, #c0392b);
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #b8860b;
}

.send-btn:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(139,0,0,0.4);
}

.send-btn:disabled { opacity: 0.5; cursor: default; }

/* 讀取動畫按鈕 */
.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.loading-spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #b8860b;
    animation: spin 0.8s linear infinite;
    margin-right: 6px;
    vertical-align: middle;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.chat-locked {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 40px 20px;
}

.chat-locked .lock-icon { font-size: 2.5rem; }
.chat-locked h4 { color: #b8860b; font-size: 1rem; margin: 12px 0; }
.chat-locked p { color: rgba(255,255,255,0.6); font-size: 0.8rem; }

/* 響應式 */
@media (max-width: 1100px) {
    .result-layout {
        flex-direction: column;
    }
    .lottery-main-scroll, .chat-panel {
        height: auto;
        min-height: 500px;
    }
    .poem-text-content {
        font-size: 1.2rem;
    }
}

@media (max-width: 700px) {
    .lottery-main-scroll {
        padding: 20px;
    }
    .poem-frame {
        padding: 15px;
    }
    .poem-text-content {
        font-size: 1rem;
    }
    .interpretation-card {
        height: auto;
        max-height: 180px;
    }
    .god-selector-container {
        gap: 20px;
    }
    .god-option {
        width: 140px;
    }
    .god-img-wrapper {
        height: 160px;
    }
    .draw-select-card {
        padding: 30px 20px;
    }
}
</style>

<div class="draw-page-wrapper">

<?php if (!$show_result): ?>
<!-- ========== 抽籤前選擇區 ========== -->
<div class="draw-select-section">
    <div class="draw-select-card">
        <h1>領受神示</h1>
        <p>閉目靜心，虔誠默念所求，選擇欲請示的神明</p>
        
        <div class="god-selector-container">
            <?php foreach($god_configs as $key => $conf): ?>
            <div class="god-option" data-god="<?= $key ?>" onclick="selectGod(this, '<?= $key ?>')">
                <div class="god-img-wrapper">
                    <img src="images/<?= $conf['img'] ?>" alt="<?= $conf['god_title'] ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23f0e6d0%22 font-size=%2214%22%3E<?= urlencode($conf['god_title']) ?>%3C/text%3E%3C/svg%3E'">
                </div>
                <span><?= $conf['god_title'] ?></span>
                <small><?= $conf['prayer'] ?></small>
                <input type="radio" name="q_type" value="<?= $key ?>" style="display: none;">
            </div>
            <?php endforeach; ?>
        </div>
        
        <div id="confirm-area">
            <button class="btn-ritual btn-primary" id="confirmBtn">開始請示</button>
        </div>
        
        <div id="divination-area" style="display: none;">
            <div id="shake-container">
                <h3 id="statusTitle">閉目誠心感應 <span id="selectedGodDisplay"></span>...</h3>
                <button class="btn-ritual btn-primary" id="divinationBtn" style="background: #8b3a2a;">擲筊請示</button>
                <div id="divination-result"></div>
                <div id="draw-btn-container" style="display: none;">
                    <form method="post">
                        <input type="hidden" name="question_type" id="finalType">
                        <button type="submit" name="draw" class="btn-ritual btn-primary" id="drawSubmitBtn">領取靈籤</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ========== 抽籤結果區 ========== -->
<div class="result-container">
    <div class="result-layout">
        
        <!-- 左側：籤詩區（組員風格） -->
        <div class="lottery-main-scroll">
            <div class="poem-header-top">
                【 <?= htmlspecialchars($current_god_title) ?> 】第 <?= htmlspecialchars($display_num) ?> 籤
            </div>

            <div class="fortune-title">
                【 <?= htmlspecialchars($current_fortune) ?> 】
            </div>

            <div class="poem-frame">
                <div class="poem-text-content">
                    <?= nl2br(htmlspecialchars($_SESSION['last_lot_text'])) ?>
                </div>
            </div>

            <div class="interpretation-card">
                <h3>傳統解籤觀點</h3>
                <p><?= nl2br(htmlspecialchars($_SESSION['last_interpretation'])) ?></p>
            </div>

            <div class="action-buttons-bottom">
                <a href="draw.php?reset=1" class="btn-ritual btn-reset">重新請示</a>
                <?php if ($user_id > 0): ?>
                <a href="history.php" class="btn-ritual btn-history">歷史紀錄</a>
                <?php endif; ?>
                <a href="index.php" class="btn-ritual btn-reset">返回首頁</a>
            </div>
        </div>
        
        <!-- 右側：AI 聊天面板 -->
        <div class="chat-panel">
            <div class="chat-header">
                <span class="chat-header-icon">智</span>
                <div>
                    <div class="chat-title">智慧解籤</div>
                    <div class="chat-subtitle">
                        <?= htmlspecialchars($current_god_title) ?> · 第 <?= htmlspecialchars($display_num) ?> 籤
                    </div>
                </div>
            </div>

            <?php if ($user_id > 0): ?>
            
            <!-- 預設問題按鈕區 -->
            <div class="quick-questions">
                <div class="quick-title">快速提問：</div>
                <div class="quick-buttons">
                    <button class="quick-btn" onclick="setQuickQuestion('此籤對於近期事業發展有何建議？')">事業發展</button>
                    <button class="quick-btn" onclick="setQuickQuestion('此籤對於感情姻緣有何啟示？')">感情姻緣</button>
                    <button class="quick-btn" onclick="setQuickQuestion('此籤對於財運與投資有何指引？')">財運投資</button>
                    <button class="quick-btn" onclick="setQuickQuestion('此籤對於健康與家運有何提醒？')">健康家運</button>
                    <button class="quick-btn" onclick="setQuickQuestion('綜合此籤意涵，請給予詳細開示')">完整開示</button>
                    <button class="quick-btn" onclick="setQuickQuestion('籤中提到的時機點是什麼時候？')">時機指引</button>
                    <button class="quick-btn" onclick="setQuickQuestion('請問有什麼需要注意的地方？')">注意事項</button>
                </div>
            </div>
            
            <!-- 聊天訊息區 -->
            <div class="chat-messages" id="chatMessages">
                <div class="bubble-row ai-row">
                    <div class="avatar ai-avatar">AI</div>
                    <div class="bubble ai-bubble">
                        您好，我已閱讀您的籤詩，有任何疑問都可以問我，點擊上方按鈕快速提問，或直接輸入您的問題。
                    </div>
                </div>
            </div>
            
            <!-- 輸入區 -->
            <div class="chat-input-area">
                <textarea id="userInput" placeholder="請輸入您想詢問的問題…" rows="1"></textarea>
                <button class="send-btn" id="sendBtn">➤</button>
            </div>
            
            <?php else: ?>
            <div class="chat-locked">
                <div class="lock-icon">鎖</div>
                <h4>此功能僅限信眾使用</h4>
                <p>登入後即可啟動智慧解籤<br>為您撥雲見日。</p>
                <a href="login.php" class="btn-ritual btn-primary" style="margin-top: 10px;">立即登入</a>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<?php if ($user_id > 0): ?>
<script>
// 籤詩資訊
const LOT_NUMBER = <?= json_encode((string)$raw_num) ?>;
const LOT_TEXT = <?= json_encode($_SESSION['last_lot_text'] ?? '') ?>;
const GOD_NAME = <?= json_encode($_SESSION['last_god_name'] ?? '') ?>;
const USER_CAT = <?= json_encode($god_configs[$q_type]['name'] ?? '綜合運勢') ?>;
const INTERPRETATION = <?= json_encode($_SESSION['last_interpretation'] ?? '') ?>;

const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
const sendBtn = document.getElementById('sendBtn');

// Enter 送出
userInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

sendBtn.addEventListener('click', sendMessage);

function setQuickQuestion(question) {
    userInput.value = question;
    sendMessage();
}

function scrollBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function appendBubble(role, text) {
    const row = document.createElement('div');
    row.className = `bubble-row ${role}-row`;

    const avatar = document.createElement('div');
    avatar.className = `avatar ${role}-avatar`;
    avatar.textContent = role === 'ai' ? 'AI' : '您';

    const bubble = document.createElement('div');
    bubble.className = `bubble ${role}-bubble`;
    bubble.style.whiteSpace = 'pre-line';
    bubble.textContent = text;

    if (role === 'ai') {
        row.appendChild(avatar);
        row.appendChild(bubble);
    } else {
        row.appendChild(bubble);
        row.appendChild(avatar);
    }

    chatMessages.appendChild(row);
    scrollBottom();
}

function showTyping() {
    const typingRow = document.createElement('div');
    typingRow.className = 'bubble-row ai-row';
    typingRow.id = 'typingIndicator';
    typingRow.innerHTML = `
        <div class="avatar ai-avatar">AI</div>
        <div class="bubble ai-bubble typing-bubble">
            <span></span><span></span><span></span>
        </div>
    `;
    chatMessages.appendChild(typingRow);
    scrollBottom();
}

function removeTyping() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}

async function sendMessage() {
    const question = userInput.value.trim();
    if (!question) return;

    appendBubble('user', question);
    userInput.value = '';
    
    sendBtn.disabled = true;
    showTyping();
    
    try {
        const response = await fetch('ajax_ai_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                lot_number: LOT_NUMBER,
                god_name: GOD_NAME,
                user_category: USER_CAT,
                user_question: question,
                lot_text: LOT_TEXT,
                interpretation: INTERPRETATION
            })
        });
        
        const data = await response.json();
        removeTyping();
        
        if (data.reply) {
            appendBubble('ai', data.reply);
        } else {
            appendBubble('ai', '系統異常：' + (data.error || '請稍後再試'));
        }
    } catch (err) {
        removeTyping();
        console.error(err);
        appendBubble('ai', '連線失敗，請確認網路後重試');
    }
    
    sendBtn.disabled = false;
}
</script>
<?php endif; ?>

<?php endif; ?>
</div>

<script>
let selectedGodKey = 'general';
let selectedGodName = '天上聖母';

function selectGod(element, godKey) {
    document.querySelectorAll('.god-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    element.classList.add('selected');
    
    const radio = element.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
    
    selectedGodKey = godKey;
    selectedGodName = element.querySelector('span').innerText;
}

document.querySelector('.god-option')?.classList.add('selected');

document.getElementById('confirmBtn')?.addEventListener('click', function() {
    const selected = document.querySelector('input[name="q_type"]:checked');
    if (!selected) {
        alert('請先選擇一位神明');
        return;
    }
    
    const selectedDiv = document.querySelector('.god-option.selected');
    const godName = selectedDiv?.querySelector('span')?.innerText || '神明';
    
    document.getElementById('finalType').value = selected.value;
    document.getElementById('confirm-area').style.display = 'none';
    document.getElementById('divination-area').style.display = 'block';
    document.getElementById('selectedGodDisplay').innerHTML = `「${godName}」`;
    
    const divinationBtn = document.getElementById('divinationBtn');
    divinationBtn.onclick = function() {
        const resDiv = document.getElementById('divination-result');
        this.disabled = true;
        resDiv.innerHTML = "正在向神明請示中...";
        
        setTimeout(() => {
            if (Math.random() * 100 < 70) {
                resDiv.innerHTML = "聖筊！神明應允。";
                document.getElementById('draw-btn-container').style.display = 'block';
                this.style.display = 'none';
            } else {
                resDiv.innerHTML = "未得感應，請誠心再試一次。";
                setTimeout(() => location.reload(), 1500);
            }
        }, 1500);
    };
});

// 領取靈籤按鈕讀取動畫
const drawSubmitBtn = document.getElementById('drawSubmitBtn');
if (drawSubmitBtn) {
    drawSubmitBtn.addEventListener('click', function() {
        this.classList.add('btn-loading');
        this.innerHTML = '<span class="loading-spinner"></span>請示中...';
    });
}
</script>

<?php include('includes/footer.php'); ?>