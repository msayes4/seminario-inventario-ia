<?php

require_once '../config/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| Solo Administrador y Encargado de Inventario
|--------------------------------------------------------------------------
*/

exigirRol(['Administrador', 'Encargado de Inventario']);

/*
|--------------------------------------------------------------------------
| Obtener productos
|--------------------------------------------------------------------------
*/

$productos = $conn->query("
    SELECT id, nombre, stock
    FROM productos
    WHERE activo = 1
    ORDER BY nombre
");

$mensaje = '';

/*
|--------------------------------------------------------------------------
| Registrar movimiento
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pid = (int)($_POST['producto_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');

    if (
        $pid > 0 &&
        $cantidad > 0 &&
        in_array($tipo, ['ENTRADA', 'SALIDA', 'AJUSTE'], true)
    ) {

        $conn->begin_transaction();

        try {

            $stmtProducto = $conn->prepare("
                SELECT stock
                FROM productos
                WHERE id = ?
                FOR UPDATE
            ");

            $stmtProducto->bind_param("i", $pid);
            $stmtProducto->execute();

            $resultado = $stmtProducto->get_result();
            $p = $resultado->fetch_assoc();

            if (!$p) {
                throw new Exception('Producto no encontrado.');
            }

            /*
            |--------------------------------------------------------------
            | Calcular nuevo stock
            |--------------------------------------------------------------
            */

            if ($tipo === 'ENTRADA') {

                $nuevo = $p['stock'] + $cantidad;

            } elseif ($tipo === 'SALIDA') {

                $nuevo = $p['stock'] - $cantidad;

            } else {

                $nuevo = $cantidad;

            }

            /*
            |--------------------------------------------------------------
            | Evitar stock negativo
            |--------------------------------------------------------------
            */

            if ($nuevo < 0) {
                throw new Exception('No hay stock suficiente.');
            }

            /*
            |--------------------------------------------------------------
            | Actualizar stock
            |--------------------------------------------------------------
            */

            $stmtUpdate = $conn->prepare("
                UPDATE productos
                SET stock = ?
                WHERE id = ?
            ");

            $stmtUpdate->bind_param(
                "ii",
                $nuevo,
                $pid
            );

            $stmtUpdate->execute();

            /*
            |--------------------------------------------------------------
            | Registrar movimiento
            |--------------------------------------------------------------
            */

            $uid = (int)$_SESSION['usuario_id'];

            $stmtMovimiento = $conn->prepare("
                INSERT INTO movimientos
                (
                    producto_id,
                    tipo,
                    cantidad,
                    motivo,
                    usuario_id
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmtMovimiento->bind_param(
                "isisi",
                $pid,
                $tipo,
                $cantidad,
                $motivo,
                $uid
            );

            $stmtMovimiento->execute();

            $conn->commit();

            $mensaje = 'Movimiento registrado correctamente.';

        } catch (Exception $e) {

            $conn->rollback();

            $mensaje = $e->getMessage();
        }

    } else {

        $mensaje = 'Datos del movimiento no válidos.';
    }
}

/*
|--------------------------------------------------------------------------
| Obtener últimos movimientos
|--------------------------------------------------------------------------
*/

$mov = $conn->query("
    SELECT
        m.*,
        p.nombre
    FROM movimientos m
    JOIN productos p
        ON p.id = m.producto_id
    ORDER BY m.id DESC
    LIMIT 30
");

?>

<!doctype html>

<html lang="es">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>Movimientos</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid p-4">

    <h2>Movimientos de inventario</h2>

    <?php if ($mensaje): ?>

        <div class="alert alert-info">
            <?= htmlspecialchars($mensaje) ?>
        </div>

    <?php endif; ?>


    <!-- Registrar movimiento -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="post" class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Producto
                    </label>

                    <select
                        name="producto_id"
                        class="form-select"
                        required
                    >

                        <?php while ($p = $productos->fetch_assoc()): ?>

                            <option value="<?= $p['id'] ?>">

                                <?= htmlspecialchars($p['nombre']) ?>

                                (stock <?= $p['stock'] ?>)

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Tipo
                    </label>

                    <select
                        name="tipo"
                        class="form-select"
                        required
                    >

                        <option value="ENTRADA">
                            ENTRADA
                        </option>

                        <option value="SALIDA">
                            SALIDA
                        </option>

                        <option value="AJUSTE">
                            AJUSTE
                        </option>

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Cantidad
                    </label>

                    <input
                        name="cantidad"
                        type="number"
                        min="1"
                        class="form-control"
                        required
                    >

                </div>


                <div class="col-md-4">

                    <label class="form-label">
                        Motivo
                    </label>

                    <input
                        name="motivo"
                        class="form-control"
                        placeholder="Compra, venta, ajuste..."
                    >

                </div>


                <div class="col-12">

                    <button class="btn btn-primary">
                        Registrar movimiento
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- Historial -->

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <h5 class="mb-3">
                Últimos movimientos
            </h5>

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($m = $mov->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($m['fecha']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['tipo']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['cantidad']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['motivo']) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>