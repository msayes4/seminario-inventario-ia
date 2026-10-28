<?php
require_once '../config/auth.php'; require_once '../config/database.php';
$productos=$conn->query("SELECT p.*,c.nombre categoria FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 ORDER BY p.id DESC");
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Productos</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>
<?php include '../includes/navbar.php'; ?><div class="container-fluid p-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Productos</h2><a class="btn btn-primary" href="crear.php">+ Nuevo producto</a></div>
<div class="card shadow-sm border-0"><div class="card-body"><table class="table table-hover"><thead><tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Compra</th><th>Venta</th><th>Stock</th><th>Estado</th><th></th></tr></thead><tbody>
<?php while($p=$productos->fetch_assoc()): ?><tr><td><?=$p['codigo']?></td><td><?=htmlspecialchars($p['nombre'])?></td><td><?=htmlspecialchars($p['categoria']??'Sin categoría')?></td><td>Q <?=number_format($p['precio_compra'],2)?></td><td>Q <?=number_format($p['precio_venta'],2)?></td><td><?=$p['stock']?></td><td><?=$p['stock']<=$p['stock_minimo']?'<span class="badge bg-warning text-dark">Bajo</span>':'<span class="badge bg-success">Normal</span>'?></td><td><a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?=$p['id']?>">Editar</a></td></tr><?php endwhile; ?>
</tbody></table></div></div></div></body></html>
