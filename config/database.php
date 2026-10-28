<?php

$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'inventario_ia';
$port = (int) (getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306');

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    error_log('Error de conexión con MySQL: ' . $conn->connect_error);
    http_response_code(500);
    die('No fue posible conectar con la base de datos.');
}

$conn->set_charset('utf8mb4');