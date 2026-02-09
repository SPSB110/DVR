<?php
/*
session_start();
// Database connection with PDO (MySQL)
$host = 'host';
$db   = 'name';
$user = 'user';
$pass = 'passwd';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
/*
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
*/
function log_calc($label, $value) {
    $logFile = __DIR__ . '/calcoli.log';
    $line = date('Y-m-d H:i:s') . ' [CALC] ' . $label . ': ' . json_encode($value) . PHP_EOL;
    error_log($line, 3, $logFile);
}

?>
