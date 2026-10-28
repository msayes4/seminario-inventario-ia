
<?php
session_start();

echo '<h2>DiagnÃ³stico de sesiÃ³n</h2>';

echo '<p>ID de usuario: ';
echo htmlspecialchars((string)($_SESSION['usuario_id'] ?? 'No hay sesiÃ³n'));
echo '</p>';

echo '<p>Nombre: ';
echo htmlspecialchars((string)($_SESSION['nombre'] ?? $_SESSION['usuario_nombre'] ?? 'No disponible'));
echo '</p>';

echo '<p>Rol detectado: ';
echo htmlspecialchars((string)($_SESSION['rol'] ?? 'No hay rol en la sesiÃ³n'));
echo '</p>';
?>
