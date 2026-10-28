<?php

require_once '../config/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| Solo Administrador y Encargado de Inventario
|--------------------------------------------------------------------------
*/

exigirRol(['Administrador', 'Encargado de Inventario']);

$id = (int)($_GET['id'] ?? 0);

$p = $conn->query("
    SELECT *
    FROM productos
    WHERE id = $id
")->fetch_assoc();

if (!$p) {
    die('Producto no encontrado');
}

$categorias = $conn->query("
    SELECT id, nombre
    FROM categorias
    ORDER BY nombre
");

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $cat = (int)($_POST['categoria_id'] ?? 0);
    $comp = (float)($_POST['precio_compra'] ?? 0);
    $venta = (float)($_POST['precio_venta'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $min = (int)($_POST['stock_minimo'] ?? 5);
    $max = (int)($_POST['stock_maximo'] ?? 50);

    $stmt = $conn->prepare("
        UPDATE productos
        SET
            codigo = ?,
            nombre = ?,
            categoria_id = ?,
            precio_compra = ?,
            precio_venta = ?,
            stock = ?,
            stock_minimo = ?,
            stock_maximo = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssidiiiii",
        $codigo,
        $nombre,
        $cat,
        $comp,
        $venta,
        $stock,
        $min,
        $max,
        $id
    );

    if ($stmt->execute()) {

        header('Location: index.php');
        exit;

    }

    $msg = $stmt->error;
}

?>

<!doctype html>

<html lang="es">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Editar producto</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container p-4">

    <h2>Editar producto</h2>

    <?php if ($msg): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($msg) ?>
        </div>

    <?php endif; ?>

    <form method="post" class="row g-3">

        <div class="col-md-4">

            <label class="form-label">
                Código
            </label>

            <input
                name="codigo"
                class="form-control"
                value="<?= htmlspecialchars($p['codigo']) ?>"
                required
            >

        </div>

        <div class="col-md-8">

            <label class="form-label">
                Nombre
            </label>

            <input
                name="nombre"
                class="form-control"
                value="<?= htmlspecialchars($p['nombre']) ?>"
                required
            >

        </div>

        <div class="col-md-4">

            <label class="form-label">
                Categoría
            </label>

            <select
                name="categoria_id"
                class="form-select"
            >

                <?php while ($c = $categorias->fetch_assoc()): ?>

                    <option
                        value="<?= $c['id'] ?>"
                        <?= $p['categoria_id'] == $c['id'] ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars($c['nombre']) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Precio compra
            </label>

            <input
                name="precio_compra"
                type="number"
                step=".01"
                class="form-control"
                value="<?= $p['precio_compra'] ?>"
            >

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Precio venta
            </label>

            <input
                name="precio_venta"
                type="number"
                step=".01"
                class="form-control"
                value="<?= $p['precio_venta'] ?>"
            >

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Stock
            </label>

            <input
                name="stock"
                type="number"
                class="form-control"
                value="<?= $p['stock'] ?>"
                min="0"
            >

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Stock mínimo
            </label>

            <input
                name="stock_minimo"
                type="number"
                class="form-control"
                value="<?= $p['stock_minimo'] ?>"
                min="0"
            >

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Stock máximo
            </label>

            <input
                name="stock_maximo"
                type="number"
                class="form-control"
                value="<?= $p['stock_maximo'] ?>"
                min="0"
            >

        </div>

        <div class="col-12">

            <button class="btn btn-primary">
                Actualizar
            </button>

            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

        </div>

    </form>

</div>

</body>

</html>