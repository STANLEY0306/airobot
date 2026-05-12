<?php
session_start();
include('includes/header.php');
?>

<style>
.about-container {
    max-width: 1100px;
    margin: 60px auto;
    padding: 0 20px;
}

.about-card {
    background: rgba(45, 20, 10, 0.88);
    border: 2px solid #b8860b;
    border-radius: 28px;
    padding: 50px 45px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.3);
    backdrop-filter: blur(2px);
}

.about-card h2 {
    text-align: center;
    color: #e8c87a;
    font-size: 2rem;
    margin-bottom: 30px;
    letter-spacing: 6px;
    font-weight: normal;
}

.about-section {
    margin-bottom: 40px;
}

.about-section h3 {
    color: #e8c87a;
    font-size: 1.3rem;
    margin-bottom: 20px;
    padding-left: 12px;
    border-left: 4px solid #8b3a2a;
    letter-spacing: 2px;
    font-weight: normal;
}

.about-section p {
    color: #d4c5a9;
    line-height: 1.8;
    font-size: 0.95rem;
    text-align: justify;
}

/* 三大神明展示區 */
.gods-row-about {
    display: flex;
    justify-content: space-between;
    gap: 25px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.god-item {
    flex: 1;
    text-align: center;
    background: rgba(0,0,0,0.3);
    border-radius: 20px;
    padding: 20px 15px;
    border: 1px solid rgba(184,134,11,0.4);
    transition: transform 0.2s;
}

.god-item:hover {
    transform: translateY(-5px);
}

.god-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #b8860b;
    margin-bottom: 12px;
}

.god-item .god-name {
    color: #e8c87a;
    font-size: 1.2rem;
    margin: 10px 0 5px;
}

.god-item .god-range {
    color: #b8860b;
    font-size: 0.8rem;
    margin: 5px 0;
}

.god-item .god-desc-mini {
    color: #d4c5a9;
    font-size: 0.8rem;
    margin-top: 8px;
}

.ratio-text {
    background: rgba(184,134,11,0.2);
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    color: #e8c87a;
    margin-top: 8px;
}

/* 特色功能列表 - 使用圖片作為功能圖 */
.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    color: #d4c5a9;
    padding: 12px 0;
    padding-left: 70px;
    position: relative;
    line-height: 1.6;
    border-bottom: 1px solid rgba(184,134,11,0.2);
}

.feature-list li:last-child {
    border-bottom: none;
}

.feature-icon {
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid #b8860b;
    background: rgba(0,0,0,0.4);
}

.feature-title {
    color: #e8c87a;
    font-weight: bold;
    margin-right: 10px;
}

.feature-desc {
    color: #c4b58a;
    font-size: 0.85rem;
}

.deco-line {
    height: 1px;
    background: linear-gradient(90deg, transparent, #b8860b, transparent);
    margin: 30px 0 20px;
}

.zen-quote {
    text-align: center;
    color: #e8c87a;
    font-size: 0.9rem;
    letter-spacing: 3px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px dashed rgba(183,134,11,0.3);
}

@media (max-width: 700px) {
    .about-card {
        padding: 30px 25px;
    }
    .about-card h2 {
        font-size: 1.6rem;
    }
    .about-section h3 {
        font-size: 1.1rem;
    }
    .gods-row-about {
        flex-direction: column;
        gap: 15px;
    }
    .feature-list li {
        padding-left: 60px;
    }
    .feature-icon {
        width: 45px;
        height: 45px;
    }
}
</style>

<div class="about-container">
    <div class="about-card">
        <h2>關於我們</h2>

        <div class="about-section">
            <h3>平台簡介</h3>
            <p>線上抽籤平台結合傳統文化與現代科技，讓每一次請示都更具意義。我們傳承古老的抽籤儀式，並以數位方式呈現，期許為信眾帶來安定的力量。</p>
        </div>

        <div class="about-section">
            <h3>服務宗旨</h3>
            <p>心誠則靈，有求必應。我們的目標是讓抽籤不僅僅是一種傳統儀式，而是一種能帶來內心平靜的數位體驗。無論是事業、感情、運勢，都希望為您指引方向。</p>
        </div>

        <div class="about-section">
            <h3>特色功能</h3>
            <ul class="feature-list">
                <li>
                    <img class="feature-icon" src="images/god1.png" alt="功能圖示" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E圖%3C/text%3E%3C/svg%3E'">
                    <span class="feature-title">完整籤詩資料庫</span>
                    <span class="feature-desc">— 天上聖母、月下老人、財神爺共 220 首靈籤</span>
                </li>
                <li>
                    <img class="feature-icon" src="images/god1.png" alt="功能圖示" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E圖%3C/text%3E%3C/svg%3E'">
                    <span class="feature-title">AI 智慧解籤</span>
                    <span class="feature-desc">— 針對您的現況提供深度解析</span>
                </li>
                <li>
                    <img class="feature-icon" src="images/god1.png" alt="功能圖示" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E圖%3C/text%3E%3C/svg%3E'">
                    <span class="feature-title">個人歷史紀錄</span>
                    <span class="feature-desc">— 隨時回顧過往靈籤</span>
                </li>
                <li>
                    <img class="feature-icon" src="images/god1.png" alt="功能圖示" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E圖%3C/text%3E%3C/svg%3E'">
                    <span class="feature-title">擲筊儀式感</span>
                    <span class="feature-desc">— 模擬傳統擲筊，誠心感應</span>
                </li>
                <li>
                    <img class="feature-icon" src="images/god1.png" alt="功能圖示" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E圖%3C/text%3E%3C/svg%3E'">
                    <span class="feature-title">響應式設計</span>
                    <span class="feature-desc">— 電腦、平板、手機皆可順暢使用</span>
                </li>
            </ul>
        </div>

        <div class="about-section">
            <h3>三大神明</h3>
            <div class="gods-row-about">
                <div class="god-item">
                    <img class="god-avatar" src="images/god1.png" alt="天上聖母" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E媽祖%3C/text%3E%3C/svg%3E'">
                    <div class="god-name">天上聖母</div>
                    <div class="god-range">60 首籤詩</div>
                    <div class="ratio-text">佔比 27%</div>
                    <div class="god-desc-mini">護國庇民 · 海上守護</div>
                </div>
                <div class="god-item">
                    <img class="god-avatar" src="images/god2.png" alt="月下老人" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E月老%3C/text%3E%3C/svg%3E'">
                    <div class="god-name">月下老人</div>
                    <div class="god-range">100 首籤詩</div>
                    <div class="ratio-text">佔比 45%</div>
                    <div class="god-desc-mini">千里姻緣 · 一線牽</div>
                </div>
                <div class="god-item">
                    <img class="god-avatar" src="images/god3.png" alt="財神爺" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%238b5a2b%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23e8c87a%22 font-size=%2212%22%3E財神%3C/text%3E%3C/svg%3E'">
                    <div class="god-name">財神爺</div>
                    <div class="god-range">60 首籤詩</div>
                    <div class="ratio-text">佔比 27%</div>
                    <div class="god-desc-mini">招財進寶 · 財源廣進</div>
                </div>
            </div>
        </div>

        <div class="deco-line"></div>

        <div class="zen-quote">
            〝 心誠則靈 · 有求必應 〞
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>