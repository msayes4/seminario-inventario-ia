<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once __DIR__ . '/ejecutar_python.php';

exigirRol(['Administrador', 'Encargado de Inventario']);

$productos = $conn->query("
    SELECT *
    FROM productos
    WHERE activo = 1
    ORDER BY nombre
");

$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pid = (int)($_POST['producto_id'] ?? 0);
    $dias = max(1, (int)($_POST['dias'] ?? 7));

    if ($pid <= 0) {
        $resultados['error'] = 'Selecciona un producto válido.';
    } else {

        $stmtProducto = $conn->prepare("
            SELECT *
            FROM productos
            WHERE id = ?
              AND activo = 1
        ");

        $stmtProducto->bind_param("i", $pid);
        $stmtProducto->execute();

        $producto = $stmtProducto->get_result()->fetch_assoc();
        $stmtProducto->close();

        if (!$producto) {
            $resultados['error'] = 'No se encontró el producto seleccionado.';
        } else {

            $stmtMovimientos = $conn->prepare("
                SELECT cantidad
                FROM movimientos
                WHERE producto_id = ?
                  AND tipo = 'SALIDA'
                ORDER BY fecha DESC
                LIMIT 7
            ");

            $stmtMovimientos->bind_param("i", $pid);
            $stmtMovimientos->execute();

            $movimientos = $stmtMovimientos->get_result();
            $valores = [];

            while ($movimiento = $movimientos->fetch_assoc()) {
                $valores[] = (float) $movimiento['cantidad'];
            }

            $stmtMovimientos->close();

            $promedio = count($valores) > 0
                ? array_sum($valores) / count($valores)
                : 0;

            $pronosticoIA = pronosticarConPython($valores, $dias);

            if ($pronosticoIA === null) {

                $resultados['error'] =
                    'No fue posible ejecutar el modelo de Python. '
                    . 'Revisa el registro de errores del servidor.';

            } else {

                $demanda = (float) $pronosticoIA['demanda_predicha'];

                $reposicion = max(
                    0,
                    (int) ceil(
                        $demanda
                        + (float) $producto['stock_minimo']
                        - (float) $producto['stock']
                    )
                );

                $modelo = 'Regresión lineal con scikit-learn';

                $stmtPronostico = $conn->prepare("
                    INSERT INTO pronosticos
                    (
                        producto_id,
                        fecha_pronostico,
                        horizonte_dias,
                        demanda_predicha,
                        modelo
                    )
                    VALUES (?, CURDATE(), ?, ?, ?)
                ");

                if ($stmtPronostico) {

                    $stmtPronostico->bind_param(
                        "iids",
                        $pid,
                        $dias,
                        $demanda,
                        $modelo
                    );

                    if ($stmtPronostico->execute()) {

                        $resultados = [
                            'producto' => $producto,
                            'promedio' => $promedio,
                            'demanda' => $demanda,
                            'reposicion' => $reposicion,
                            'modelo' => $modelo,
                            'observaciones' => (int) (
                                $pronosticoIA['observaciones'] ?? 0
                            )
                        ];

                    } else {
                        error_log(
                            'No se pudo guardar el pronóstico: '
                            . $stmtPronostico->error
                        );

                        $resultados['error'] =
                            'El cálculo terminó, pero no se pudo guardar '
                            . 'el pronóstico en la base de datos.';
                    }

                    $stmtPronostico->close();

                } else {
                    error_log(
                        'No se pudo preparar el INSERT de pronósticos: '
                        . $conn->error
                    );

                    $resultados['error'] =
                        'No se pudo preparar el registro del pronóstico.';
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pronóstico IA</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container p-4">

    <h2 class="mb-2">Pronóstico de stock</h2>

    <p class="text-muted">
        Modelo predictivo basado en regresión lineal con Python
        y scikit-learn, utilizando los últimos movimientos de salida.
    </p>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <form method="post" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Producto</label>

                    <select
                        name="producto_id"
                        class="form-select"
                        required
                    >
                        <option value="">Seleccione un producto</option>

                        <?php if ($productos): ?>
                            <?php while ($p = $productos->fetch_assoc()): ?>
                                <option
                                    value="<?= (int) $p['id'] ?>"
                                    <?= (
                                        (int) ($_POST['producto_id'] ?? 0)
                                        === (int) $p['id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Horizonte (días)</label>

                    <input
                        name="dias"
                        type="number"
                        min="1"
                        value="<?= (int) ($_POST['dias'] ?? 7) ?>"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Calcular pronóstico
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php if (!empty($resultados['error'])): ?>
        <div class="alert alert-danger mt-4">
            <?= htmlspecialchars($resultados['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($resultados['producto'])): ?>

        <?php
        $producto = $resultados['producto'];
        $promedio = $resultados['promedio'];
        $demanda = $resultados['demanda'];
        $reposicion = $resultados['reposicion'];
        ?>

        <div class="row g-3">

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <small class="text-muted">Stock actual</small>
                    <h2><?= (int) $producto['stock'] ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <small class="text-muted">
                        Promedio por movimiento
                    </small>
                    <h2><?= number_format($promedio, 2) ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <small class="text-muted">Demanda estimada</small>
                    <h2><?= number_format($demanda, 2) ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <small class="text-muted">Reposición sugerida</small>
                    <h2><?= $reposicion ?></h2>
                </div>
            </div>

        </div>

        <div class="alert alert-info mt-3">
            <strong>Modelo utilizado:</strong>
            <?= htmlspecialchars($resultados['modelo']) ?><br>

            <strong>Movimientos analizados:</strong>
            <?= $resultados['observaciones'] ?><br>

            <strong>Horizonte:</strong>
            <?= (int) ($_POST['dias'] ?? 7) ?> días
        </div>

        <div class="alert alert-<?= $reposicion > 0 ? 'warning' : 'success' ?> mt-3">

            <strong><?= htmlspecialchars($producto['nombre']) ?>:</strong>

            <?php if ($reposicion > 0): ?>
                Se recomienda evaluar una reposición de
                <?= $reposicion ?> unidades.
            <?php else: ?>
                Según la estimación, no se requiere reposición adicional
                para alcanzar el stock mínimo durante el período.
            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
