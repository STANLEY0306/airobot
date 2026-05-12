<?php
include('includes/db_connect.php');

$sql = "SELECT * FROM lots ORDER BY RAND() LIMIT 1";
$result = $mysqli->query($sql);

if($result && $row = $result->fetch_assoc()){
    $lot_name = htmlspecialchars($row['name']);
    $lot_desc = nl2br(htmlspecialchars($row['description']));
    $desc_parts = explode("/", $lot_desc);

    echo "<h3>$lot_name</h3><ul>";
    foreach($desc_parts as $part){
        echo "<li>$part</li>";
    }
    echo "</ul>";
}else{
    echo "<p style='color:red;'>抽籤失敗，請稍後再試。</p>";
}
?>
