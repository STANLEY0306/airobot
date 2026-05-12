<?php
session_start();
include('includes/db_connect.php');
include('includes/header.php');

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id <= 0) {
    echo "<script>alert('請先登入'); window.location.href='login.php';</script>";
    exit;
}

/* ===== 1. 左側統計：神明次數 ===== */
$stats_query = $mysqli->prepare("SELECT god_name, COUNT(*) as count FROM lottery_history WHERE user_id = ? GROUP BY god_name");
$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats_res = $stats_query->get_result();
$stats_data = ['天上聖母' => 0, '月下老人' => 0, '財神爺' => 0];
while ($s_row = $stats_res->fetch_assoc()) {
    $stats_data[$s_row['god_name']] = $s_row['count'];
}
$stats_query->close();

/* ===== 2. 近期運勢趨勢 (最近10次) ===== */
$trend_stmt = $mysqli->prepare("
    SELECT fortune_level, DATE_FORMAT(created_at, '%m/%d') as short_date 
    FROM lottery_history 
    WHERE user_id = ? 
    ORDER BY created_at ASC 
    LIMIT 10
");
$trend_stmt->bind_param("i", $user_id);
$trend_stmt->execute();
$trend_res = $trend_stmt->get_result();
$trend_labels = [];
$trend_values = [];
$fortune_weights = [
    '大吉' => 5, '上吉' => 4.5, '中吉' => 3.5, '小吉' => 3,
    '中平' => 2.5, '平' => 2, '下下' => 1, '下下籤' => 1
];
while ($t_row = $trend_res->fetch_assoc()) {
    $trend_labels[] = $t_row['short_date'];
    $trend_values[] = $fortune_weights[$t_row['fortune_level']] ?? 2.5;
}
$trend_stmt->close();

/* ===== 3. 分頁邏輯 ===== */
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

$total_stmt = $mysqli->prepare("SELECT COUNT(*) FROM lottery_history WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_stmt->bind_result($total_rows);
$total_stmt->fetch();
$total_stmt->close();
$total_pages = max(1, ceil($total_rows / $per_page));

$stmt = $mysqli->prepare("
    SELECT * FROM lottery_history 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
/* ===== 歷史紀錄頁面 - 與主站風格統一 ===== */
.history-full-wrapper {
    max-width: 1400px;
    margin: 40px auto;
    padding: 0 20px;
}

.history-layout {
    display: flex;
    gap: 30px;
    align-items: stretch;
}

/* 左側統計面板 */
.stats-panel {
    flex: 3;
    background: rgba(45, 20, 10, 0.92);
    border: 2px solid #b8860b;
    border-radius: 24px;
    padding: 25px;
    backdrop-filter: blur(2px);
    height: fit-content;
    position: sticky;
    top: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.stats-panel h3 {
    color: #e8c87a;
    text-align: center;
    font-size: 1.3rem;
    margin-bottom: 15px;
    border-bottom: 1px solid #b8860b;
    display: inline-block;
    width: 100%;
    letter-spacing: 2px;
}

.chart-box {
    background: rgba(0,0,0,0.3);
    border-radius: 16px;
    padding: 15px;
    margin-bottom: 25px;
}

.stat-numbers {
    margin-top: 20px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(183,134,11,0.3);
    color: #f5e6c8;
    font-size: 0.95rem;
}

.stat-row.total {
    border-top: 1px solid #b8860b;
    margin-top: 8px;
    padding-top: 12px;
    font-weight: bold;
    color: #e8c87a;
}

/* 右側歷史清單 */
.history-list {
    flex: 7;
}

.history-title {
    text-align: center;
    font-size: 1.8rem;
    color: #e8c87a;
    margin-bottom: 25px;
    letter-spacing: 4px;
}

.history-card {
    background: #fef7e8;
    background-image: url('images/scripture-texture.jpg');
    background-size: cover;
    background-blend-mode: overlay;
    background-color: rgba(255, 248, 235, 0.9);
    border: 1px solid #b8860b;
    border-left: 8px solid #8b0000;
    border-radius: 20px;
    padding: 20px 25px;
    margin-bottom: 25px;
    box-shadow: 0 8px 18px rgba(0,0,0,0.15);
    transition: transform 0.2s;
}

.history-card:hover {
    transform: translateY(-3px);
}

.history-header {
    display: flex;
    align-items: baseline;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(139,0,0,0.2);
}

.god-name {
    font-size: 1.2rem;
    font-weight: bold;
    color: #8b3a2a;
}

.lottery-num {
    font-size: 1rem;
    color: #b8860b;
    font-weight: bold;
}

.fortune-level {
    font-size: 0.9rem;
    padding: 2px 12px;
    border-radius: 30px;
    background: #8b3a2a;
    color: #fef7e8;
}

.poem-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #2c1a10;
    margin: 12px 0;
    font-style: italic;
}

.poem-text small {
    color: #8b6b4a;
}

.interpretation-short {
    font-size: 0.85rem;
    color: #4a2a18;
    border-left: 3px solid #b8860b;
    padding-left: 12px;
    margin: 10px 0;
}

.history-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    flex-wrap: wrap;
    gap: 12px;
}

.ai-re-ask-btn {
    background: #8b3a2a;
    border: none;
    padding: 8px 18px;
    border-radius: 40px;
    color: #fef7e8;
    cursor: pointer;
    font-family: "標楷體", serif;
    transition: all 0.2s;
    letter-spacing: 1px;
}

.ai-re-ask-btn:hover {
    background: #9e4a38;
    transform: translateY(-2px);
}

.time {
    font-size: 0.75rem;
    color: #8b6b4a;
}

/* 分頁 */
.pagination {
    text-align: center;
    margin-top: 30px;
    margin-bottom: 20px;
}

.pagination a, .pagination span {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 4px;
    background: rgba(45, 20, 10, 0.85);
    border: 1px solid #b8860b;
    border-radius: 40px;
    color: #f5e6c8;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination a:hover {
    background: #b8860b;
    color: #2c1a10;
}

.pagination .active {
    background: #b8860b;
    color: #2c1a10;
    cursor: default;
    pointer-events: none;
}

/* 無資料 */
.no-data {
    background: rgba(45,20,10,0.7);
    border-radius: 20px;
    padding: 60px 20px;
    text-align: center;
    color: #f5e6c8;
    font-size: 1.1rem;
}

/* 響應式 */
@media (max-width: 900px) {
    .history-layout {
        flex-direction: column;
    }
    .stats-panel {
        position: static;
        width: 100%;
    }
    .history-card {
        padding: 15px;
    }
}
</style>

<div class="history-full-wrapper">
    <div class="history-layout">
        <!-- 左側統計區 -->
        <aside class="stats-panel">
            <h3>請示分布</h3>
            <div class="chart-box">
                <canvas id="godStatsChart" width="300" height="180" style="max-width:100%; height:auto;"></canvas>
            </div>
            <div class="stat-numbers">
                <div class="stat-row"><span>天上聖母</span><span><?= $stats_data['天上聖母'] ?> 次</span></div>
                <div class="stat-row"><span>月下老人</span><span><?= $stats_data['月下老人'] ?> 次</span></div>
                <div class="stat-row"><span>財神爺</span><span><?= $stats_data['財神爺'] ?> 次</span></div>
                <div class="stat-row total"><span>總請示次數</span><span><?= array_sum($stats_data) ?> 次</span></div>
            </div>

            <h3 style="margin-top: 30px;">運勢趨勢</h3>
            <div class="chart-box">
                <canvas id="trendChart" width="300" height="150" style="max-width:100%; height:auto;"></canvas>
            </div>
        </aside>

        <!-- 右側歷史清單 -->
        <main class="history-list">
            <h2 class="history-title">抽籤歷史紀錄</h2>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    // 計算顯示籤號
                    $num = $row['number'];
                    if ($row['god_name'] === '月下老人') $num -= 60;
                    elseif ($row['god_name'] === '財神爺') $num -= 160;
                    // 簡短擷取籤詩
                    $short_poem = mb_substr($row['poem_text'], 0, 50) . (mb_strlen($row['poem_text']) > 50 ? '…' : '');
                ?>
                <div class="history-card">
                    <div class="history-header">
                        <span class="god-name">【<?= htmlspecialchars($row['god_name']) ?>】</span>
                        <span class="lottery-num">第 <?= $num ?> 籤</span>
                        <span class="fortune-level"><?= htmlspecialchars($row['fortune_level']) ?></span>
                    </div>
                    <div class="poem-text">
                        <?= nl2br(htmlspecialchars($short_poem)) ?>
                    </div>
                    <div class="interpretation-short">
                        <?= nl2br(htmlspecialchars(mb_substr($row['interpretation'], 0, 80))) ?>…
                    </div>
                    <div class="history-footer">
                        <form method="post" action="draw.php" style="margin:0;">
                            <input type="hidden" name="re_ask_history" value="1">
                            <input type="hidden" name="lot_number" value="<?= $row['number'] ?>">
                            <input type="hidden" name="god_name" value="<?= htmlspecialchars($row['god_name']) ?>">
                            <input type="hidden" name="poem_text" value="<?= htmlspecialchars($row['poem_text']) ?>">
                            <input type="hidden" name="interpretation" value="<?= htmlspecialchars($row['interpretation']) ?>">
                            <input type="hidden" name="fortune_level" value="<?= $row['fortune_level'] ?>">
                            <input type="hidden" name="q_type" value="<?= $row['question_type'] ?>">
                            <button type="submit" class="ai-re-ask-btn">智慧解籤</button>
                        </form>
                        <div class="time">請示時間：<?= htmlspecialchars($row['created_at']) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>尚無抽籤紀錄，誠心祈求必有感應。</p>
                    <a href="draw.php" class="btn-ritual btn-primary" style="margin-top:15px; display:inline-block;">前往抽籤</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
    // 神明分布圓餅圖
    new Chart(document.getElementById('godStatsChart'), {
        type: 'doughnut',
        data: {
            labels: ['天上聖母', '月下老人', '財神爺'],
            datasets: [{
                data: [<?= $stats_data['天上聖母'] ?>, <?= $stats_data['月下老人'] ?>, <?= $stats_data['財神爺'] ?>],
                backgroundColor: ['#c62828', '#d4af37', '#5d4037'],
                borderColor: '#e8c87a',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#f5e6c8', font: { size: 11 } } }
            }
        }
    });

    // 運勢趨勢折線圖
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($trend_labels) ?>,
            datasets: [{
                data: <?= json_encode($trend_values) ?>,
                borderColor: '#d4af37',
                backgroundColor: 'rgba(212, 175, 55, 0.2)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#e8c87a'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0.5, max: 5.5, ticks: { stepSize: 1, callback: (val) => ['','平','中平','小吉','中吉','大吉'][val] , color: '#e8c87a' } },
                x: { ticks: { color: '#e8c87a' }, grid: { display: false } }
            }
        }
    });
</script>

<?php
$stmt->close();
include('includes/footer.php');
?>