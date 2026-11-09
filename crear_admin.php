<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Acceso no permitido.');
}

$key = $_POST['key'] ?? '';
$password = $_POST['password'] ?? '';

$configuredKey = getenv('IMPORT_KEY') ?: '';

if (
    $configuredKey === '' ||
    !is_string($key) ||
    !hash_equals($configuredKey, $key)
) {
    http_response_code(403);
    exit('Acceso denegado.');
}

if (!is_string($password) || strlen($password) < 12) {
    http_response_code(400);
    exit('La contraseña debe tener al menos 12 caracteres.');
}

$correo = 'admin@inventario.com';
$nombre = 'Administrador del Sistema';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "SELECT id FROM usuarios WHERE correo = ? LIMIT 1"
);

if (!$stmt) {
    exit('Error al consultar usuarios: ' . $conn->error);
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if ($usuario) {
    $stmt = $conn->prepare(
        "UPDATE usuarios SET password = ?, rol = 'Administrador',
         estado = 1 WHERE id = ?"
    );
    $stmt->bind_param("si", $hash, $usuario['id']);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO usuarios
         (nombre, correo, password, rol, estado)
         VALUES (?, ?, ?, 'Administrador', 1)"
    );
    $stmt->bind_param("sss", $nombre, $correo, $hash);
}

if (!$stmt->execute()) {
    error_log('Error al crear administrador: ' . $stmt->error);
    exit('No se pudo crear el administrador.');
}

echo 'Administrador creado o actualizado correctamente.';
$stmt->close();