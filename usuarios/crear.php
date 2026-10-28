
<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Solo el administrador puede crear usuarios.
exigirRol(['Administrador']);
$mensaje = '';
$tipoMensaje = 'danger';

$roles = [
    'Administrador',
    'Encargado de Inventario',
    'Vendedor',
    'Consulta'
];

$nombre = '';
$correo = '';
$rol = 'Vendedor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $rol = $_POST['rol'] ?? 'Vendedor';
    $password = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmacion'] ?? '';

    if ($nombre === '' || $correo === '' || $password === '') {
        $mensaje = 'Completa todos los campos obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Escribe un correo electrónico válido.';
    } elseif (!in_array($rol, $roles, true)) {
        $mensaje = 'El rol seleccionado no es válido.';
    } elseif (strlen($password) < 8) {
        $mensaje = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirmacion) {
        $mensaje = 'Las contraseñas no coinciden.';
    } else {

        // Comprobar si el correo ya está registrado.
        $stmt = $conn->prepare(
            "SELECT id FROM usuarios WHERE correo = ? LIMIT 1"
        );

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $mensaje = 'Ya existe un usuario con ese correo electrónico.';
        } else {

            // Guardar la contraseña con un hash seguro.
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO usuarios (nombre, correo, password, rol, estado)
                 VALUES (?, ?, ?, ?, 1)"
            );

            $stmt->bind_param(
                "ssss",
                $nombre,
                $correo,
                $hash,
                $rol
            );

            if ($stmt->execute()) {
                header('Location: /usuarios/index.php?creado=1');
                exit;
            } else {
                $mensaje = 'No fue posible crear el usuario. Inténtalo nuevamente.';
            }

            $stmt->close();
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear usuario - Inventario IA</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container p-4" style="max-width: 800px;">

    <h2>Crear usuario</h2>
    <p class="text-muted">
        Registra una cuenta nueva y asigna sus permisos.
    </p>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form method="post">

                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        maxlength="100"
                        value="<?= htmlspecialchars($nombre) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input
                        type="email"
                        name="correo"
                        class="form-control"
                        maxlength="150"
                        value="<?= htmlspecialchars($correo) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol del usuario</label>
                    <select name="rol" class="form-select" required>
                        <?php foreach ($roles as $opcion): ?>
                            <option
                                value="<?= htmlspecialchars($opcion) ?>"
                                <?= $rol === $opcion ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <small class="text-muted">
                        Debe tener al menos 8 caracteres.
                    </small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmar contraseña</label>
                    <input
                        type="password"
                        name="confirmacion"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success">
                    Guardar usuario
                </button>

                <a href="/usuarios/index.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
