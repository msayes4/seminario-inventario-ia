<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
$rol = $_SESSION['rol'] ?? '';

/*
|--------------------------------------------------------------------------
| Resumen del inventario
|--------------------------------------------------------------------------
*/

$totalProductos = 0;
$stockBajo = 0;
$totalCategorias = 0;
$totalMovimientos = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalProductos = (int)$row['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
    AND stock <= stock_minimo
");

if ($result) {
    $row = $result->fetch_assoc();
    $stockBajo = (int)$row['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM categorias
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalCategorias = (int)$row['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM movimientos
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalMovimientos = (int)$row['total'];
}

?>

<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Dashboard - Inventario IA</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">

    <div class="mb-4">

        <h2>
            Dashboard
        </h2>

        <p class="text-muted mb-0">

            Bienvenido,
            <strong>
                <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
            </strong>.

            Rol:
            <strong>
                <?= htmlspecialchars($rol) ?>
            </strong>

        </p>

    </div>


    <!-- RESUMEN -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Productos activos
                    </small>

                    <h2 class="mt-2">
                        <?= $totalProductos ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Productos con stock bajo
                    </small>

                    <h2 class="mt-2">
                        <?= $stockBajo ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Categorías
                    </small>

                    <h2 class="mt-2">
                        <?= $totalCategorias ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Movimientos registrados
                    </small>

                    <h2 class="mt-2">
                        <?= $totalMovimientos ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- ACCESOS -->

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <h4 class="mb-4">
                Accesos rápidos
            </h4>

            <div class="row g-3">


                <!-- PRODUCTOS -->

                <div class="col-md-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <h5>
                                Productos
                            </h5>

                            <p class="text-muted">
                                Consultar los productos registrados en el inventario.
                            </p>

                            <a
                                href="/productos/index.php"
                                class="btn btn-primary"
                            >
                                Ver productos
                            </a>

                        </div>

                    </div>

                </div>


                <!-- CREAR PRODUCTO -->

                <?php if (in_array(
                    $rol,
                    ['Administrador', 'Encargado de Inventario'],
                    true
                )): ?>

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-body">

                                <h5>
                                    Nuevo producto
                                </h5>

                                <p class="text-muted">
                                    Registrar un nuevo producto en el inventario.
                                </p>

                                <a
                                    href="/productos/crear.php"
                                    class="btn btn-success"
                                >
                                    Crear producto
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- MOVIMIENTOS -->

                <?php if (in_array(
                    $rol,
                    ['Administrador', 'Encargado de Inventario'],
                    true
                )): ?>

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-body">

                                <h5>
                                    Movimientos
                                </h5>

                                <p class="text-muted">
                                    Registrar entradas y salidas de inventario.
                                </p>

                                <a
                                    href="/inventario/movimientos.php"
                                    class="btn btn-warning"
                                >
                                    Gestionar movimientos
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- PRONÓSTICO -->

                <?php if (in_array(
                    $rol,
                    ['Administrador', 'Encargado de Inventario'],
                    true
                )): ?>

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-body">

                                <h5>
                                    Pronóstico IA
                                </h5>

                                <p class="text-muted">
                                    Consultar la demanda estimada y reposición sugerida.
                                </p>

                                <a
                                    href="/pronostico/index.php"
                                    class="btn btn-info"
                                >
                                    Ver pronóstico
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- REPORTES -->

                <?php if (in_array(
                    $rol,
                    ['Administrador', 'Consulta'],
                    true
                )): ?>

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-body">

                                <h5>
                                    Reportes
                                </h5>

                                <p class="text-muted">
                                    Consultar información general del inventario.
                                </p>

                                <a
                                    href="/reportes/index.php"
                                    class="btn btn-secondary"
                                >
                                    Ver reportes
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    </div>

</div>

</body>

</html>
