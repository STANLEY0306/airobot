<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('includes/db_connect.php');
include('includes/header.php');

// 神明設定
$god_configs = [
    'mazu' => ['name' => '天上聖母', 'min' => 1, 'max' => 60, 'display_name' => '天上聖母'],
    'yuelao' => ['name' => '月下老人', 'min' => 61, 'max' => 160, 'display_name' => '月下老人'],
    'caishen' => ['name' => '財神爺', 'min' => 161, 'max' => 220, 'display_name' => '財神爺']
];

// 處理搜尋
$search_error = '';
$result_data = null;
$total_rows = 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

$selected_god = isset($_GET['god']) && isset($god_configs[$_GET['god']]) ? $_GET['god'] : 'mazu';
$lot_number = isset($_GET['lot_number']) && $_GET['lot_number'] !== '' ? intval($_GET['lot_number']) : null;

$god_min = $god_configs[$selected_god]['min'];
$god_max = $god_configs[$selected_god]['max'];

// 驗證籤號是否在範圍內
$is_valid = true;
if ($lot_number !== null) {
    if ($lot_number < $god_min || $lot_number > $god_max) {
        $search_error = "⚠️ 找不到這籤詩！" . $god_configs[$selected_god]['display_name'] . " 的籤號範圍是 " . $god_min . " ~ " . $god_max . " 號。";
        $is_valid = false;
    }
}

// 構建查詢
if ($is_valid && $lot_number !== null) {
    // 精確查詢單一籤號
    $sql = "SELECT number, poem_text, interpretation FROM lottery_poems WHERE number = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $lot_number);
    $stmt->execute();
    $result_data = $stmt->get_result();
    $total_rows = $result_data->num_rows;
    if ($total_rows == 0 && empty($search_error)) {
        $search_error = "⚠️ 查無此籤號，請確認後再試。";
    }
} else if ($is_valid) {
    // 分頁查詢該神明所有籤詩
    $sql = "SELECT number, poem_text, interpretation FROM lottery_poems WHERE number BETWEEN ? AND ? ORDER BY number ASC LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iiii", $god_min, $god_max, $per_page, $offset);
    $stmt->execute();
    $result_data = $stmt->get_result();
    
    // 取得總筆數
    $count_sql = "SELECT COUNT(*) as total FROM lottery_poems WHERE number BETWEEN ? AND ?";
    $count_stmt = $mysqli->prepare($count_sql);
    $count_stmt->bind_param("ii", $god_min, $god_max);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_rows = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
}

$total_pages = ($per_page > 0) ? ceil($total_rows / $per_page) : 1;

// 取得籤詩顯示用的函數
function getGodNameByNumber($number) {
    if ($number >= 1 && $number <= 60) return '天上聖母';
    if ($number >= 61 && $number <= 160) return '月下老人';
    if ($number >= 161 && $number <= 220) return '財神爺';
    return '未知';
}
?>

<style>
/* ===== 籤詩資料庫專用樣式 - 滿版固定 ===== */
.poem-library {
    width: 100%;
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, rgba(93, 64, 55, 0.88), rgba(45, 20, 10, 0.92)), url('images/scripture-texture.jpg');
    background-size: cover;
    background-attachment: fixed;
    padding: 40px 0;
}

.library-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 30px;
}

/* 標題區 */
.library-header {
    text-align: center;
    margin-bottom: 40px;
}

.library-header h1 {
    font-size: 2.8rem;
    color: #b8860b;
    text-shadow: 3px 3px 6px rgba(0,0,0,0.5);
    letter-spacing: 4px;
    margin-bottom: 10px;
}

.library-header p {
    color: #f5e8c8;
    font-size: 1rem;
    opacity: 0.8;
}

/* 搜尋卡片 - 固定高度 */
.search-card {
    background: rgba(30, 15, 8, 0.9);
    border: 2px solid #b8860b;
    border-radius: 20px;
    padding: 25px 35px;
    margin-bottom: 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
}

.search-form {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: center;
}

.search-group {
    flex: 1;
    min-width: 180px;
}

.search-group label {
    display: block;
    color: #b8860b;
    font-size: 0.85rem;
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.search-group select,
.search-group input {
    width: 100%;
    padding: 12px 16px;
    background: #f5e8c8;
    border: 1px solid #b8860b;
    border-radius: 12px;
    font-family: "標楷體", serif;
    font-size: 1rem;
    color: #2c1810;
    cursor: pointer;
    transition: all 0.2s;
}

.search-group select:focus,
.search-group input:focus {
    outline: none;
    border-color: #c62828;
    box-shadow: 0 0 8px rgba(198, 40, 40, 0.5);
}

.search-btn {
    background: linear-gradient(45deg, #c62828, #cd7f32);
    border: none;
    padding: 12px 32px;
    border-radius: 40px;
    color: #f5e8c8;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.4s ease;
    font-family: "標楷體", serif;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 15px rgba(183, 134, 11, 0.4);
}

.reset-btn {
    background: rgba(100, 70, 50, 0.8);
    border: 1px solid #b8860b;
    padding: 12px 28px;
    border-radius: 40px;
    color: #f5e8c8;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.4s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.reset-btn:hover {
    background: #8d6e63;
}

.error-message {
    text-align: center;
    padding: 20px;
    background: rgba(198, 40, 40, 0.3);
    border: 1px solid #c62828;
    border-radius: 12px;
    color: #ffaaaa;
    margin-bottom: 30px;
}

/* 籤詩網格 - 固定卡片高度 */
.poem-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

/* 固定高度的籤詩卡片 */
.poem-card {
    background: linear-gradient(145deg, #fff8eb, #f5e6c8);
    border: 2px solid #b8860b;
    border-radius: 16px;
    padding: 0;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 380px;
}

.poem-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.4);
}

/* 卡片頭部 - 硃砂印章風格 */
.poem-card-header {
    background: linear-gradient(135deg, #c62828, #8b0000);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 10px;
    border-bottom: 2px solid #b8860b;
}

.poem-number {
    font-size: 1.6rem;
    font-weight: bold;
    color: #b8860b;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    font-family: "標楷體", serif;
}

.poem-number small {
    font-size: 1rem;
    color: #f5e8c8;
}

.poem-god-badge {
    background: rgba(0,0,0,0.5);
    padding: 5px 14px;
    border-radius: 30px;
    color: #b8860b;
    font-size: 0.85rem;
    font-weight: bold;
}

/* 籤詩內文區 - 可捲動 */
.poem-content {
    padding: 20px 22px;
    flex: 1;
    overflow-y: auto;
    background: url('images/scripture-texture.jpg') repeat;
    background-blend-mode: overlay;
    background-color: rgba(255, 248, 235, 0.7);
}

.poem-text {
    font-size: 1.15rem;
    line-height: 1.85;
    color: #2c1810;
    font-family: "標楷體", serif;
    text-align: center;
    font-weight: 500;
    margin-bottom: 15px;
    word-break: break-word;
}

/* 自訂捲軸 */
.poem-content::-webkit-scrollbar {
    width: 6px;
}

.poem-content::-webkit-scrollbar-track {
    background: rgba(139, 69, 19, 0.2);
    border-radius: 3px;
}

.poem-content::-webkit-scrollbar-thumb {
    background: #b8860b;
    border-radius: 3px;
}

.poem-desc {
    font-size: 0.85rem;
    line-height: 1.55;
    color: #5a3e2b;
    border-top: 1px dashed rgba(139, 69, 19, 0.3);
    padding-top: 12px;
    margin-top: 8px;
}

.poem-desc strong {
    color: #8b0000;
}

/* 分頁 */
.pagination-area {
    text-align: center;
    padding: 20px 0;
}

.pagination-area a,
.pagination-area span {
    display: inline-block;
    padding: 10px 18px;
    margin: 0 6px;
    background: rgba(30, 15, 8, 0.8);
    border: 1px solid #b8860b;
    border-radius: 40px;
    color: #f5e8c8;
    text-decoration: none;
    transition: all 0.4s ease;
    font-size: 0.95rem;
}

.pagination-area a:hover {
    background: #b8860b;
    color: #2c1810;
}

.pagination-area .current-page {
    background: #b8860b;
    color: #2c1810;
}

.pagination-area .disabled {
    opacity: 0.4;
    pointer-events: none;
}

.stats-info {
    text-align: center;
    margin-bottom: 25px;
    color: #f5e8c8;
    font-size: 0.9rem;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    background: rgba(30, 15, 8, 0.7);
    border-radius: 20px;
    color: #f5e8c8;
    font-size: 1.2rem;
}

/* 響應式 - 保持卡片高度固定 */
@media (max-width: 900px) {
    .poem-grid {
        grid-template-columns: 1fr;
    }
    .poem-card {
        height: 360px;
    }
    .library-container {
        padding: 0 20px;
    }
    .search-group {
        min-width: 140px;
    }
}

@media (max-width: 600px) {
    .poem-card {
        height: 340px;
    }
    .poem-text {
        font-size: 1rem;
    }
    .poem-card-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>

<div class="poem-library">
    <div class="library-container">
        
        <!-- 標題區 -->
        <div class="library-header">
            <h1>📜 靈籤寶庫</h1>
            <p>虔心參閱 • 感應神意</p>
        </div>
        
        <!-- 搜尋區 -->
        <div class="search-card">
            <form method="GET" action="poem_library.php" class="search-form">
                <div class="search-group">
                    <label>🏮 請示神明</label>
                    <select name="god" id="godSelect">
                        <?php foreach ($god_configs as $key => $god): ?>
                            <option value="<?php echo $key; ?>" <?php echo $selected_god == $key ? 'selected' : ''; ?>>
                                <?php echo $god['display_name']; ?> (<?php echo $god['min']; ?> ~ <?php echo $god['max']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="search-group">
                    <label>🔢 籤詩編號（選填）</label>
                    <input type="number" name="lot_number" id="lotNumber" placeholder="直接輸入籤號查詢" value="<?php echo htmlspecialchars($lot_number ?? ''); ?>">
                </div>
                <div class="search-group">
                    <button type="submit" class="search-btn">🔍 請示靈籤</button>
                </div>
                <div class="search-group">
                    <a href="poem_library.php" class="reset-btn">🪶 重置查詢</a>
                </div>
            </form>
        </div>
        
        <!-- 錯誤訊息 -->
        <?php if ($search_error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php endif; ?>
        
        <!-- 統計資訊 -->
        <?php if ($total_rows > 0 && empty($search_error)): ?>
            <div class="stats-info">
                🌟 共找到 <?php echo $total_rows; ?> 支靈籤 
                <?php if ($lot_number): ?>
                    - 為您呈現完整籤文
                <?php else: ?>
                    - 第 <?php echo $page; ?> / <?php echo $total_pages; ?> 頁
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- 籤詩列表 - 固定高度網格 -->
        <?php if ($result_data && $result_data->num_rows > 0 && empty($search_error)): ?>
            <div class="poem-grid">
                <?php while ($row = $result_data->fetch_assoc()): 
                    $god_name = getGodNameByNumber($row['number']);
                    $short_desc = mb_substr($row['interpretation'], 0, 70) . (mb_strlen($row['interpretation']) > 70 ? '...' : '');
                ?>
                    <div class="poem-card">
                        <div class="poem-card-header">
                            <span class="poem-number">第 <?php echo $row['number']; ?> 籤 <small>｜ <?php echo $god_name; ?></small></span>
                            <span class="poem-god-badge">✧ 靈籤 ✧</span>
                        </div>
                        <div class="poem-content">
                            <div class="poem-text">
                                <?php echo nl2br(htmlspecialchars($row['poem_text'])); ?>
                            </div>
                            <div class="poem-desc">
                                <strong>📖 籤解提要：</strong><br>
                                <?php echo htmlspecialchars($short_desc); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- 分頁 -->
            <?php if (!$lot_number && $total_pages > 1): ?>
                <div class="pagination-area">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&god=<?php echo $selected_god; ?>">◀ 上一頁</a>
                    <?php else: ?>
                        <span class="disabled">◀ 上一頁</span>
                    <?php endif; ?>
                    
                    <span class="current-page">第 <?php echo $page; ?> 頁</span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&god=<?php echo $selected_god; ?>">下一頁 ▶</a>
                    <?php else: ?>
                        <span class="disabled">下一頁 ▶</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif (empty($search_error) && $total_rows == 0): ?>
            <div class="no-data">
                🎋 暫無籤詩資料<br>
                <span style="font-size: 0.9rem;">請確認資料庫是否已匯入籤詩</span>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$mysqli->close();
include('includes/footer.php');
?>