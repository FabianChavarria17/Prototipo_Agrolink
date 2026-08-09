<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('consumidor');
$u = usuarioActual();
$db = getDB();

$busqueda  = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

$sql = "SELECT p.*, ag.nombre AS agricultor_nombre, ag.ubicacion AS agricultor_ubicacion,
               ag.calificacion_promedio, ag.id AS agricultor_id
        FROM productos p
        JOIN usuarios ag ON ag.id = p.agricultor_id
        WHERE p.activo = 1 AND p.cantidad_kg > 0";
$params = [];

if ($busqueda !== '') {
    $sql .= ' AND p.nombre LIKE ?';
    $params[] = '%' . $busqueda . '%';
}
if ($categoria !== '') {
    $sql .= ' AND p.categoria = ?';
    $params[] = $categoria;
}

// HU-15: sin coordenadas reales en el prototipo, se aproxima "cercanía" comparando
// el texto de ubicación del consumidor con el del agricultor (mismo cantón primero).
$sql .= ' ORDER BY (ag.ubicacion = ?) DESC, p.creado_en DESC';
$params[] = $u['ubicacion'] ?? '';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $db->query('SELECT DISTINCT categoria FROM productos ORDER BY categoria')->fetchAll(PDO::FETCH_COLUMN);

$tituloPagina = 'Catálogo';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Catálogo de productos</h1>

<form method="get" class="filtros">
    <input type="text" name="q" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>">
    <select name="categoria">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $categoria === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn secundario">Filtrar</button>
</form>

<?php if (!$productos): ?>
    <div class="card empty-state">No se encontraron productos con esos filtros.</div>
<?php else: ?>
<div class="grid">
    <?php foreach ($productos as $p): ?>
    <a href="producto.php?id=<?= $p['id'] ?>" class="card producto-card">
        <div class="miniatura">
            <?php if ($p['imagen']): ?>
                <img src="assets/img/productos/<?= htmlspecialchars($p['imagen']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
            <?php else: ?>🍅<?php endif; ?>
        </div>
        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
        <div class="meta"><?= htmlspecialchars($p['categoria']) ?> · <?= number_format($p['cantidad_kg'], 1) ?> kg disp.</div>
        <div class="precio"><?= formatoColones($p['precio_crc']) ?> / kg</div>
        <div class="meta">
            📍 <?= htmlspecialchars($p['agricultor_nombre']) ?>
            <?php if ($p['agricultor_ubicacion'] === ($u['ubicacion'] ?? '___')): ?><span class="tag verde">Cerca de ti</span><?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
