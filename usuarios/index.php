<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Solo el administrador puede gestionar usuarios.
exigirRol('Administrador');

$sql = "
    SELECT id, nombre, correo, rol, estado, creado_en
    FROM usuarios
    ORDER BY id DESC
";

$resultado = $conn->query($sql);

if (!$resultado) {
    die('No se pudo consultar la lista de usuarios.');
}

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AdministraciÃ³n de usuarios</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container-fluid p-4">


<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>AdministraciÃ³n de usuarios</h2>
        <p class="text-muted mb-0">
            Consulta los usuarios registrados y sus permisos.
        </p>
    </div>

    <a href="/usuarios/crear.php" class="btn btn-success">
        + Crear usuario
    </a>
</div>


    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo electrÃ³nico</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha de registro</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($usuario = $resultado->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <?= (int) $usuario['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['nombre']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['correo']) ?>
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($usuario['rol']) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ((int) $usuario['estado'] === 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($usuario['creado_en'] ?? '') ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    <a href="/dashboard/index.php" class="btn btn-secondary mt-3">
        Volver al Dashboard
    </a>

</div>

</body>
</html>
