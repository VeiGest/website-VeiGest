<?php
$config = require 'common/config/main-local.php';
try {
    // Conectar sem especificar banco
    $dsn = str_replace('veigest_db', '', $config['components']['db']['dsn']);
    $pdo = new PDO($dsn, $config['components']['db']['username'], $config['components']['db']['password']);
    $pdo->exec('DROP DATABASE IF EXISTS veigest_db');
    $pdo->exec('CREATE DATABASE veigest_db');
    echo 'Database dropped and recreated successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}