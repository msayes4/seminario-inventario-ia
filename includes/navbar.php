```php
<?php
$rol = $_SESSION['rol'] ?? '';
?>

<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="/dashboard/index.php">
            Inventario IA
        </a>

        <div class="navbar-nav">

            <a class="nav-link" href="/dashboard/index.php">
                Dashboard
            </a>

            <?php if (in_array($rol, ['Administrador', 'Encargado de Inventario', 'Vendedor', 'Consulta'], true)): ?>
                <a class="nav-link" href="/productos/index.php">
                    Productos
                </a>
            <?php endif; ?>

            <?php if (in_array($rol, ['Administrador', 'Encargado de Inventario'], true)): ?>
                <a class="nav-link" href="/inventario/movimientos.php">
                    Movimientos
                </a>
            <?php endif; ?>

            <?php if (in_array($rol, ['Administrador', 'Encargado de Inventario'], true)): ?>
                <a class="nav-link" href="/pronostico/index.php">
                    Pronóstico IA
                </a>
            <?php endif; ?>

            <?php if (in_array($rol, ['Administrador', 'Consulta'], true)): ?>
                <a class="nav-link" href="/reportes/index.php">
                    Reportes
                </a>
            <?php endif; ?>

            <?php if ($rol === 'Administrador'): ?>
                <a class="nav-link" href="/usuarios/index.php">
                    Usuarios
                </a>
            <?php endif; ?>

            <a class="nav-link text-warning" href="/auth/logout.php">
                Salir
            </a>

        </div>

    </div>
</nav>
```
