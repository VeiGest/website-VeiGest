<?php
$pdo = new PDO('mysql:host=localhost;dbname=veigest', 'root', '');
$stmt=$pdo->query("SHOW COLUMNS FROM users");
$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r){echo $r['Field'].": ".$r['Type']."\n";}
