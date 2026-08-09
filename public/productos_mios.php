<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('agricultor');
$u = usuarioActual();
$db = getDB();

// Actualizar cantidad en línea (HU-06)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $pid = (int) $_POST['producto_id'];
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'actualizar_stock') {
        $nuevaCantidad = (float) $_POST['cantidad_kg'];
        if ($nuevaCantidad >= 0) {
            $stmt = $db->prepare('UPDATE productos SET cantidad_kg = ? WHERE id = ? AND agricultor_id = ?');
            $stmt->execute([$nuevaCantidad, $pid, $u['id']]);
            setFlash('exito', 'Inventario actualizado.');
        }
    } elseif ($accion === 'desactivar') {
        $stmt = $db->prepare('UPDATE productos SET activo = 0 WHERE id = ? AND agricultor_id = ?');
        $stmt->execute([$pid, $u['id']]);
        setFlash('exito', 'Producto retirado del catálogo.');
    } elseif ($accion === 'activar') {
        $stmt = $db->prepare('UPDATE productos SET activo = 1 WHERE id = ? AND agricultor_id = ?');
        $stmt->execute([$pid, $u['id']]);
        setFlash('exito', 'Producto vuelto a publicar.');
    }
    header('Location: productos_mios.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM productos WHERE agricultor_id = ? ORDER BY creado_en DESC');
$stmt->execute([$u['id']]);
$productos = $stmt->fetchAll();

$tituloPagina = 'Mi inventario';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Mi inventario</h1>
<p class="meta">Actualiza la cantidad disponible en tiempo real para evitar sobreventas.</p>
<p><a href="productos_publicar.php" class="btn">+ Publicar nuevo producto</a></p>

<?php if (!$productos): ?>
    <div class="card empty-state">Aún no has publicado productos. <a href="productos_publicar.php">Publica el primero</a>.</div>
<?php else: ?>
<div class="card">
<table>
    <tr><th>Producto</th><th>Categoría</th><th>Precio/kg</th><th>Stock (kg)</th><th>Estado</th><th></th></tr>
    <?php foreach ($productos as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['nombre']) ?></td>
        <td><?= htmlspecialchars($p['categoria']) ?></td>
        <td><?= formatoColones($p['precio_crc']) ?></td>
        <td>
            <form method="post" style="display:flex; gap:6px;">
                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="accion" value="actualizar_stock">
                <input type="number" step="0.01" min="0" name="cantidad_kg" value="<?= $p['cantidad_kg'] ?>" style="width:90px;">
                <button type="submit" class="btn pequeno secundario">Guardar</button>
            </form>
        </td>
        <td>
            <?php if ($p['activo']): ?>
                <span class="tag verde">Publicado</span>
            <?php else: ?>
                <span class="tag gris">Retirado</span>
            <?php endif; ?>
        </td>
        <td>
            <form method="post" <?= $p['activo'] ? 'data-confirm="¿Retirar ' . htmlspecialchars($p['nombre'], ENT_QUOTES) . ' del catálogo?"' : '' ?>>
                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="accion" value="<?= $p['activo'] ? 'desactivar' : 'activar' ?>">
                <button type="submit" class="btn pequeno <?= $p['activo'] ? 'peligro' : '' ?>">
                    <?= $p['activo'] ? 'Retirar' : 'Republicar' ?>
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
