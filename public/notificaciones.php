<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
$u = usuarioActual();
$db = getDB();

// Marcar todas como leídas al visitar la bandeja.
$db->prepare('UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?')->execute([$u['id']]);

$stmt = $db->prepare('SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY creado_en DESC LIMIT 30');
$stmt->execute([$u['id']]);
$notis = $stmt->fetchAll();

$angosto = true;
$tituloPagina = 'Notificaciones';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Notificaciones</h1>

<?php if (!$notis): ?>
    <div class="card empty-state">No tienes notificaciones todavía.</div>
<?php else: ?>
    <?php foreach ($notis as $n): ?>
    <div class="card" style="padding:14px;">
        <?= htmlspecialchars($n['mensaje']) ?>
        <div class="meta"><?= date('d/m/Y H:i', strtotime($n['creado_en'])) ?></div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
