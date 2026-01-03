<?php
$pdo = new PDO('mysql:host=localhost;dbname=veigest', 'root', '');
$rows = $pdo->query("SELECT id, username, status, verification_token, company_id, nome FROM users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
var_dump($rows);
