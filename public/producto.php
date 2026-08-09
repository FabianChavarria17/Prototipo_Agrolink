<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('consumidor');
$u = usuarioActual();
$db = getDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare(
    'SELECT p.*, ag.nombre AS agricultor_nombre, ag.ubicacion AS agricultor_ubicacion, ag.id AS agricultor_id
     FROM productos p JOIN usuarios ag ON ag.id = p.agricultor_id
     WHERE p.id = ? AND p.activo = 1'
);
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: catalogo.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = (float) ($_POST['cantidad_kg'] ?? 0);
    if ($cantidad > 0 && $cantidad <= $p['cantidad_kg']) {
        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
        $_SESSION['carrito'][$id] = [
            'producto_id' => $p['id'],
            'nombre' => $p['nombre'],
            'precio_crc' => $p['precio_crc'],
            'cantidad_kg' => ($_SESSION['carrito'][$id]['cantidad_kg'] ?? 0) + $cantidad,
            'agricultor_id' => $p['agricultor_id'],
            'agricultor_nombre' => $p['agricultor_nombre'],
        ];
        setFlash('exito', 'Agregado al carrito.');
        header('Location: producto.php?id=' . $id);
        exit;
    }
    $error = 'Cantidad inválida o mayor al stock disponible.';
}

$tituloPagina = $p['nombre'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dos-columnas">
    <div>
        <div class="card">
            <div class="miniatura" style="height:220px; font-size:3.5rem;">
                <?php if ($p['imagen']): ?>
                    <img src="assets/img/productos/<?= htmlspecialchars($p['imagen']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                <?php else: ?>🥬<?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($p['nombre']) ?></h1>
            <span class="tag"><?= htmlspecialchars($p['categoria']) ?></span>
            <p><?= nl2br(htmlspecialchars($p['descripcion'] ?: 'Sin descripción adicional.')) ?></p>
            <div class="precio" style="font-size:1.3rem;"><?= formatoColones($p['precio_crc']) ?> / kg</div>
            <p class="meta"><?= number_format($p['cantidad_kg'], 1) ?> kg disponibles</p>
        </div>
    </div>
    <div>
        <div class="card">
            <h3>Vendido por</h3>
            <p><a href="perfil_publico.php?id=<?= $p['agricultor_id'] ?>"><?= htmlspecialchars($p['agricultor_nombre']) ?></a></p>
            <p class="meta">📍 <?= htmlspecialchars($p['agricultor_ubicacion'] ?? 'Costa Rica') ?></p>
        </div>
        <div class="card">
            <?php if (isset($error)): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Cantidad (kg)</label>
                    <input type="number" step="0.1" min="0.1" max="<?= $p['cantidad_kg'] ?>" name="cantidad_kg"
                           value="1" required data-precio-kg="<?= $p['precio_crc'] ?>">
                </div>
                <p class="meta">Subtotal: <span id="subtotal-vivo"><?= formatoColones($p['precio_crc']) ?></span></p>
                <button type="submit" class="btn ancho">Agregar al carrito</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
