<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
$db = getDB();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ? AND tipo = "agricultor"');
$stmt->execute([$id]);
$ag = $stmt->fetch();
if (!$ag) { header('Location: index.php'); exit; }

$stmt = $db->prepare('SELECT * FROM productos WHERE agricultor_id = ? AND activo = 1 ORDER BY creado_en DESC');
$stmt->execute([$id]);
$productos = $stmt->fetchAll();

$stmt = $db->prepare(
    'SELECT r.*, c.nombre AS consumidor_nombre FROM resenas r
     JOIN usuarios c ON c.id = r.consumidor_id
     WHERE r.agricultor_id = ? ORDER BY r.creado_en DESC LIMIT 5'
);
$stmt->execute([$id]);
$resenas = $stmt->fetchAll();

$tituloPagina = $ag['nombre'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h1><?= htmlspecialchars($ag['nombre']) ?></h1>
    <p class="meta">📍 <?= htmlspecialchars($ag['ubicacion'] ?? 'Costa Rica') ?>
        <?php if ($ag['zona_cobertura_km']): ?> · Entrega hasta <?= $ag['zona_cobertura_km'] ?> km<?php endif; ?>
    </p>
    <p class="stars">⭐ <?= number_format($ag['calificacion_promedio'], 1) ?> / 5.0</p>
    <p><?= nl2br(htmlspecialchars($ag['bio'] ?: 'Este agricultor aún no ha agregado una biografía.')) ?></p>
</div>

<h2>Productos disponibles</h2>
<?php if (!$productos): ?>
    <div class="card empty-state">Sin productos publicados actualmente.</div>
<?php else: ?>
<div class="grid">
    <?php foreach ($productos as $p): ?>
    <a href="producto.php?id=<?= $p['id'] ?>" class="card producto-card">
        <div class="miniatura">🥬</div>
        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
        <div class="precio"><?= formatoColones($p['precio_crc']) ?> / kg</div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<h2>Reseñas recientes</h2>
<?php if (!$resenas): ?>
    <p class="meta">Aún no tiene reseñas.</p>
<?php else: ?>
    <?php foreach ($resenas as $r): ?>
    <div class="card">
        <strong><?= str_repeat('⭐', (int) $r['calificacion']) ?></strong>
        — <?= htmlspecialchars($r['consumidor_nombre']) ?>
        <?php if ($r['comentario']): ?><p class="meta"><?= htmlspecialchars($r['comentario']) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
