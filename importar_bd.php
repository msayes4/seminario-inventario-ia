
<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Acceso únicamente mediante POST.');
}

$claveConfigurada = getenv('IMPORT_KEY') ?: '';
$claveRecibida = $_POST['key'] ?? '';

if (
    $claveConfigurada === '' ||
    !is_string($claveRecibida) ||
    !hash_equals($claveConfigurada, $claveRecibida)
) {
    http_response_code(403);
    exit('Acceso denegado. Verifica la clave temporal.');
}

$archivo = __DIR__ . '/database/inventario_publico.sql';

if (!is_file($archivo) || !is_readable($archivo)) {
    http_response_code(500);
    exit('No se encuentra el archivo SQL.');
}

$sql = file_get_contents($archivo);

if ($sql === false) {
    http_response_code(500);
    exit('No se pudo leer el archivo SQL.');
}

$sql = preg_replace(
    '/^\s*CREATE\s+DATABASE\b[^;]*;\s*/im',
    '',
    $sql
);

$sql = preg_replace(
    '/^\s*USE\s+`?inventario_ia`?\s*;\s*/im',
    '',
    $sql
);

if (!$conn->multi_query($sql)) {
    error_log('Error al importar SQL: ' . $conn->error);
    http_response_code(500);
    exit('La importación falló. Revisa los registros de la aplicación.');
}

do {
    $resultado = $conn->store_result();

    if ($resultado instanceof mysqli_result) {
        $resultado->free();
    }

    if (!$conn->more_results()) {
        break;
    }

    if (!$conn->next_result()) {
        error_log('Error al continuar importación SQL: ' . $conn->error);
        http_response_code(500);
        exit('La importación se detuvo. Revisa los registros de la aplicación.');
    }
} while (true);

echo 'Importación terminada. Verifica las tablas en Blitz.';
