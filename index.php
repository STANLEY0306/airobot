<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('includes/db_connect.php');
include('includes/header.php');

// 獲取抽籤統計資料
$totalQuery = $mysqli->query("SELECT COUNT(*) as total FROM lottery_history");
$totalResult = $totalQuery->fetch_assoc();
$totalDraws = $totalResult['total'] ?? 0;

$godStats = [];
$godQuery = $mysqli->query("SELECT god_name, COUNT(*) as count FROM lottery_history GROUP BY god_name");
while ($row = $godQuery->fetch_assoc()) {
    $godStats[$row['god_name']] = $row['count'];
}

$mazuCount = $godStats['天上聖母'] ?? 0;
$yuelaoCount = $godStats['月下老人'] ?? 0;
$caishenCount = $godStats['財神爺'] ?? 0;
$maxCount = max($mazuCount, $yuelaoCount, $caishenCount, 1);
?>

<style>
/* ===== 滿版震撼寺廟風格 ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #2e1f03;
}

/* 首頁滿版容器 */
.home-full {
    width: 100%;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 0;
}
/* ----- 頂部極簡標題（滿版橫向）----- */
.hero-area {
    width: 100%;
    padding: 80px 40px 40px;
    text-align: center;
    border-bottom: 1px solid rgba(232, 200, 122, 0.2);
}

.hero-icon {
    font-size: 4.5rem;
    filter: drop-shadow(0 0 15px rgba(232, 200, 122, 0.4));
    margin-bottom: 15px;
}

.hero-area h1 {
    font-size: 3.2rem;
    color: #e8c87a;
    letter-spacing: 12px;
    font-weight: normal;
    text-shadow: 0 0 20px rgba(0,0,0,0.5);
    margin: 0;
}

.hero-area p {
    font-size: 1rem;
    color: #c4b896;
    letter-spacing: 4px;
    margin-top: 12px;
}

/* ----- 三大神殿區（滿版展開）----- */
.gods-sanctuary {
    width: 100%;
    max-width: 1400px;
    margin: 60px auto;
    padding: 0 40px;
    flex: 1;
}

.gods-row {
    display: flex;
    justify-content: center;
    align-items: stretch;
    gap: 50px;
    flex-wrap: wrap;
}

/* 神殿卡片 - 大器、滿版感 */
.god-shrine {
    flex: 1;
    min-width: 280px;
    background: rgba(25, 12, 6, 0.65);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(232, 200, 122, 0.35);
    border-radius: 32px;
    padding: 45px 30px 50px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

/* 發光邊框效果 */
.god-shrine::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #e8c87a, #8b3a2a, #e8c87a);
    border-radius: 34px;
    opacity: 0;
    z-index: -1;
    transition: opacity 0.4s;
}

.god-shrine:hover::before {
    opacity: 0.5;
}

.god-shrine:hover {
    transform: translateY(-15px);
    border-color: #e8c87a;
    box-shadow: 0 30px 50px rgba(0,0,0,0.5), 0 0 30px rgba(232, 200, 122, 0.2);
}

.god-shrine.selected {
    border-color: #e8c87a;
    box-shadow: 0 0 40px rgba(232, 200, 122, 0.3);
    background: rgba(35, 18, 10, 0.8);
}

/* 神像區 */
.god-image {
    width: 180px;
    height: 180px;
    margin: 0 auto 25px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e8c87a;
    box-shadow: 0 0 25px rgba(0,0,0,0.5);
    transition: all 0.3s;
}

.god-shrine:hover .god-image {
    transform: scale(1.03);
    border-color: #c0392b;
    box-shadow: 0 0 30px rgba(232, 200, 122, 0.4);
}

.god-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.god-name {
    font-size: 1.9rem;
    color: #e8c87a;
    letter-spacing: 4px;
    margin-bottom: 12px;
    font-weight: normal;
}

.god-range {
    font-size: 1rem;
    color: #d4c5a9;
    margin-bottom: 15px;
    letter-spacing: 1px;
}

.god-desc {
    font-size: 0.9rem;
    color: #b4a480;
    line-height: 1.7;
    margin-top: 10px;
}

.select-badge {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 1.8rem;
    color: #e8c87a;
    opacity: 0;
    transition: opacity 0.2s;
}

.god-shrine.selected .select-badge {
    opacity: 1;
}

/* ----- 抽籤行動區（大器按鈕）----- */
.draw-action {
    width: 100%;
    text-align: center;
    padding: 40px 20px 60px;
}

.mega-draw-btn {
    display: inline-block;
    background: linear-gradient(135deg, #8b3a2a, #6b2518);
    border: 2px solid #e8c87a;
    padding: 20px 80px;
    font-size: 1.8rem;
    font-weight: bold;
    color: #f5e8c8;
    text-decoration: none;
    border-radius: 80px;
    transition: all 0.3s;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    letter-spacing: 8px;
    backdrop-filter: blur(4px);
}

.mega-draw-btn:hover {
    transform: translateY(-6px);
    background: linear-gradient(135deg, #9e4a38, #8b3a2a);
    box-shadow: 0 0 25px rgba(232, 200, 122, 0.5);
    letter-spacing: 10px;
}

/* ----- 統計資料區（底部橫條，不搶戲）----- */
stats-footer {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto 40px;
    padding: 0 30px;
}

.stats-mini-card {
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(4px);
    border-radius: 60px;
    padding: 18px 30px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    border: 1px solid rgba(232, 200, 122, 0.2);
}

.total-badge {
    display: flex;
    align-items: baseline;
    gap: 8px;
    color: #e8c87a;
}

.total-badge .label {
    font-size: 0.8rem;
    letter-spacing: 2px;
}

.total-badge .number {
    font-size: 1.6rem;
    font-weight: bold;
}

.god-stats-row {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.god-stat-mini {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
}

.god-stat-mini .name {
    color: #c4b896;
}

.god-stat-mini .count {
    color: #e8c87a;
    font-weight: bold;
}

/* 彈窗 */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    max-width: 450px;
    width: 85%;
    background: #1f1209;
    border: 2px solid #e8c87a;
    border-radius: 28px;
    padding: 35px 30px;
    text-align: center;
    position: relative;
}

.modal-content h3 {
    color: #e8c87a;
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.modal-content p {
    color: #d4c5a9;
    line-height: 1.7;
    margin-bottom: 20px;
}

.modal-close {
    position: absolute;
    top: 12px;
    right: 18px;
    font-size: 1.6rem;
    color: #e8c87a;
    cursor: pointer;
}

.modal-btn {
    background: #8b3a2a;
    border: 1px solid #e8c87a;
    padding: 8px 28px;
    border-radius: 40px;
    color: #f5e8c8;
    cursor: pointer;
}

/* 響應式 */
@media (max-width: 1000px) {
    .hero-area { padding: 50px 20px 30px; }
    .hero-area h1 { font-size: 2.2rem; letter-spacing: 6px; }
    .gods-sanctuary { padding: 0 25px; }
    .gods-row { gap: 30px; }
    .god-shrine { padding: 35px 20px; }
    .god-image { width: 140px; height: 140px; }
    .god-name { font-size: 1.5rem; }
    .mega-draw-btn { font-size: 1.3rem; padding: 15px 50px; letter-spacing: 6px; }
    .stats-mini-card { flex-direction: column; text-align: center; border-radius: 30px; }
}

@media (max-width: 700px) {
    .gods-row { flex-direction: column; align-items: center; }
    .god-shrine { max-width: 380px; width: 100%; }
    .hero-area h1 { font-size: 1.6rem; letter-spacing: 4px; }
    .hero-icon { font-size: 3rem; }
}
</style>

<div class="home-full">

    <!-- 滿版英雄區 -->
    <div class="hero-area">
        <div class="hero-icon">🛕</div>
        <h1>線上抽籤平台</h1>
        <p>心誠則靈 · 有求必應</p>
    </div>

<!-- 三大神殿（滿版展開） -->
    <div class="gods-sanctuary">
        <div class="gods-row">
            
            <div class="god-shrine" data-god="mazu" data-min="1" data-max="60" data-name="天上聖母" 
                 data-desc="又稱媽祖、天后，是海上的守護神。她慈悲為懷，護國庇民，保佑航海平安、闔家順遂。" 
                 data-prayer="護國庇民，海上守護。祈求平安順遂，萬事安康。" 
                 data-range="1 ~ 60">
                <div class="god-image">
                    <img src="images/god1.png" alt="天上聖母" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2214%22%3E媽祖%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="god-name">天上聖母</div>
                <div class="god-range">共60首籤詩</div>
                <div class="god-desc">護國庇民 · 海上守護</div>
                <div class="select-badge">✓</div>
            </div>

            <div class="god-shrine" data-god="yuelao" data-min="61" data-max="160" data-name="月下老人" 
                 data-desc="掌管姻緣之神，牽動天下有情人的紅線。誠心祈求，良緣自來。" 
                 data-prayer="千里姻緣一線牽，祈求良緣美滿，白首偕老。" 
                 data-range="61 ~ 160">
                <div class="god-image">
                    <img src="images/god2.png" alt="月下老人" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2214%22%3E月老%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="god-name">月下老人</div>
                <div class="god-range">共100首籤詩</div>
                <div class="god-desc">千里姻緣 · 一線牽</div>
                <div class="select-badge">✓</div>
            </div>

            <div class="god-shrine" data-god="caishen" data-min="161" data-max="220" data-name="財神爺" 
                 data-desc="招財進寶，財源廣進。掌管人間財富，誠心參拜，富足安康。" 
                 data-prayer="招財進寶，財源廣進。祈求富足安康，事業興旺。" 
                 data-range="161 ~ 220">
                <div class="god-image">
                    <img src="images/god3.png" alt="財神爺" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2214%22%3E財神%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="god-name">財神爺</div>
                <div class="god-range">共60首籤詩</div>
                <div class="god-desc">招財進寶 · 財源廣進</div>
                <div class="select-badge">✓</div>
            </div>
        </div>
    </div>

    <!-- 抽籤行動區 -->
    <div class="draw-action">
        <a href="draw.php" class="mega-draw-btn" id="drawBtn">開始抽籤</a>
    </div>

    <!-- 統計資料區（底部低調） -->
    <div class="stats-footer">
        <div class="stats-mini-card">
            <div class="total-badge">
                <span class="label">累積請示</span>
                <span class="number"><?= number_format($totalDraws) ?></span>
                <span class="label">次</span>
            </div>
            <div class="god-stats-row">
                <div class="god-stat-mini"><span class="name">天上聖母</span><span class="count"><?= number_format($mazuCount) ?>次</span></div>
                <div class="god-stat-mini"><span class="name">月下老人</span><span class="count"><?= number_format($yuelaoCount) ?>次</span></div>
                <div class="god-stat-mini"><span class="name">財神爺</span><span class="count"><?= number_format($caishenCount) ?>次</span></div>
            </div>
        </div>
    </div>

</div>

<!-- 彈窗 -->
<div id="godModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h3 id="modalGodName">天上聖母</h3>
        <p id="modalGodDesc">護國庇民，海上守護神。祈求平安順遂。</p>
        <button class="modal-btn" onclick="closeModal()">闔上</button>
    </div>
</div>

<script>
let selectedGodData = {
    god_key: 'mazu',
    god_name: '天上聖母',
    god_min: 1,
    god_max: 60
};

function openModal(godName, godDesc, godPrayer, godRange) {
    document.getElementById('modalGodName').innerText = godName;
    document.getElementById('modalGodDesc').innerHTML = `${godDesc}<br><br>🙏 ${godPrayer}`;
    document.getElementById('godModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('godModal').style.display = 'none';
}

function selectGod(element, godKey, godName, godMin, godMax) {
    document.querySelectorAll('.god-shrine').forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
    
    selectedGodData = {
        god_key: godKey,
        god_name: godName,
        god_min: godMin,
        god_max: godMax
    };
    
    sessionStorage.setItem('selectedGod', JSON.stringify(selectedGodData));
}

document.querySelectorAll('.god-shrine').forEach(shrine => {
    const godKey = shrine.getAttribute('data-god');
    const godName = shrine.getAttribute('data-name');
    const godMin = parseInt(shrine.getAttribute('data-min'));
    const godMax = parseInt(shrine.getAttribute('data-max'));
    const godDesc = shrine.getAttribute('data-desc');
    const godPrayer = shrine.getAttribute('data-prayer');
    const godRange = shrine.getAttribute('data-range');
    
    shrine.addEventListener('click', function(e) {
        e.stopPropagation();
        selectGod(this, godKey, godName, godMin, godMax);
        openModal(godName, godDesc, godPrayer, godRange);
    });
});

document.getElementById('drawBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    sessionStorage.setItem('selectedGod', JSON.stringify(selectedGodData));
    window.location.href = `draw.php?god=${selectedGodData.god_key}&min=${selectedGodData.god_min}&max=${selectedGodData.god_max}`;
});

document.addEventListener('DOMContentLoaded', function() {
    const defaultShrine = document.querySelector('.god-shrine[data-god="mazu"]');
    if (defaultShrine) {
        defaultShrine.classList.add('selected');
    }
});

window.onclick = function(event) {
    const modal = document.getElementById('godModal');
    if (event.target == modal) {
        closeModal();
    }
};
</script>

<?php include('includes/footer.php'); ?>