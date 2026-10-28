<?php

require_once '../config/auth.php';
require_once '../config/database.php';

exigirRol(['Administrador', 'Consulta']);

$rows = $conn->query("
    SELECT
        p.nombre,
        p.stock,
        p.stock_minimo,
        p.stock_maximo,
        COALESCE(
            SUM(
                CASE
                    WHEN m.tipo = 'SALIDA'
                    THEN m.cantidad
                    ELSE 0
                END
            ),
            0
        ) AS salidas
    FROM productos p
    LEFT JOIN movimientos m
        ON m.producto_id = p.id
    GROUP BY p.id
    ORDER BY salidas DESC
");
?>

<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Reportes</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container p-4">

    <h2>Reporte de inventario</h2>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped">

                    <thead>

                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>Máximo</th>
                            <th>Salidas históricas</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($r = $rows->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($r['nombre']) ?>
                            </td>

                            <td>
                                <?= $r['stock'] ?>
                            </td>

                            <td>
                                <?= $r['stock_minimo'] ?>
                            </td>

                            <td>
                                <?= $r['stock_maximo'] ?>
                            </td>

                            <td>
                                <?= $r['salidas'] ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>