<?php include('includes/header.php'); ?>
<section class="contact">
    <h2>聯絡我們</h2>
    <form method="post">
        <label>姓名：</label>
        <input type="text" name="name" required>
        <label>電子郵件：</label>
        <input type="email" name="email" required>
        <label>留言內容：</label>
        <textarea name="message" required></textarea>
        <button type="submit" class="btn">送出</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        echo "<p class='thanks'>感謝您的來信，$name！我們會儘快回覆。</p>";
    }
    ?>
</section>
<?php include('includes/footer.php'); ?>