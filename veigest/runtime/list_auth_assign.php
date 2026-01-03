<?php
$db = new PDO('mysql:host=localhost;dbname=veigest','root','');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->query('SELECT user_id,item_name FROM auth_assignment ORDER BY user_id');
foreach ($stmt as $r) {
    echo $r['user_id'] . "\t" . $r['item_name'] . "\n";
}
