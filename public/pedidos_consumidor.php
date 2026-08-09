<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('consumidor');
$u = usuarioActual();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int) $_POST['pedido_id'];
    $accion = $_POST['accion'] ?? '';

    $stmt = $db->prepare('SELECT * FROM pedidos WHERE id = ? AND consumidor_id = ?');
    $stmt->execute([$pedidoId, $u['id']]);
    $pedido = $stmt->fetch();

    if ($pedido && $accion === 'confirmar_entrega' && $pedido['estado'] === 'en_camino') {
        // HU-12: libera el pago en custodia al confirmar recepción conforme.
        $db->prepare('UPDATE pedidos SET estado = "pago_liberado" WHERE id = ?')->execute([$pedidoId]);
        crearNotificacion($pedido['agricultor_id'], 'El consumidor confirmó la entrega del pedido #' . $pedidoId . '. Pago liberado.');
        setFlash('exito', 'Entrega confirmada. El pago fue liberado al agricultor.');
    } elseif ($pedido && $accion === 'calificar') {
        $calificacion = (int) $_POST['calificacion'];
        $comentario = trim($_POST['comentario'] ?? '');
        if ($calificacion >= 1 && $calificacion <= 5) {
            $stmt = $db->prepare(
                'INSERT INTO resenas (pedido_id, agricultor_id, consumidor_id, calificacion, comentario)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$pedidoId, $pedido['agricultor_id'], $u['id'], $calificacion, $comentario]);

            // Recalcular promedio del agricultor.
            $avg = $db->prepare('SELECT AVG(calificacion) FROM resenas WHERE agricultor_id = ?');
            $avg->execute([$pedido['agricultor_id']]);
            $promedio = round((float) $avg->fetchColumn(), 2);
            $db->prepare('UPDATE usuarios SET calificacion_promedio = ? WHERE id = ?')->execute([$promedio, $pedido['agricultor_id']]);

            setFlash('exito', '¡Gracias por tu reseña!');
        }
    }
    header('Location: pedidos_consumidor.php');
    exit;
}

$stmt = $db->prepare(
    'SELECT pe.*, ag.nombre AS agricultor_nombre,
            r.id AS resena_id
     FROM pedidos pe
     JOIN usuarios ag ON ag.id = pe.agricultor_id
     LEFT JOIN resenas r ON r.pedido_id = pe.id
     WHERE pe.consumidor_id = ? ORDER BY pe.creado_en DESC'
);
$stmt->execute([$u['id']]);
$pedidos = $stmt->fetchAll();

$etiquetas = [
    'pendiente' => ['Pendiente de confirmación', 'amarillo'],
    'aceptado' => ['En preparación', 'verde'],
    'en_camino' => ['En camino', 'verde'],
    'entregado' => ['Entregado', 'gris'],
    'rechazado' => ['Rechazado', 'rojo'],
    'pago_liberado' => ['Entregado · pago liberado', 'gris'],
];

$tituloPagina = 'Mis pedidos';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Mis pedidos</h1>

<?php if (!$pedidos): ?>
    <div class="card empty-state">Aún no has realizado pedidos. <a href="catalogo.php">Explora el catálogo</a>.</div>
<?php else: ?>
    <?php foreach ($pedidos as $p): [$etiqueta, $color] = $etiquetas[$p['estado']] ?? [$p['estado'], 'gris']; ?>
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <strong>Pedido #<?= $p['id'] ?> · <?= htmlspecialchars($p['agricultor_nombre']) ?></strong>
            <span class="tag <?= $color ?>"><?= $etiqueta ?></span>
        </div>
        <p class="meta">Total: <?= formatoColones($p['total_crc']) ?> · Pago: <?= strtoupper($p['metodo_pago'] ?? '—') ?></p>

        <?php if ($p['estado'] === 'en_camino'): ?>
            <form method="post">
                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="accion" value="confirmar_entrega">
                <button class="btn pequeno">Confirmar que recibí mi pedido</button>
            </form>
        <?php elseif ($p['estado'] === 'pago_liberado' && !$p['resena_id']): ?>
            <form method="post" style="margin-top:10px;">
                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="accion" value="calificar">
                <div class="form-group">
                    <label>Califica al agricultor</label>
                    <div class="star-rating">
                        <input type="hidden" name="calificacion" value="5">
                        <span class="estrella" data-valor="1">★</span><span class="estrella" data-valor="2">★</span><span class="estrella" data-valor="3">★</span><span class="estrella" data-valor="4">★</span><span class="estrella" data-valor="5">★</span>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="comentario" rows="2" placeholder="Comentario opcional..."></textarea>
                </div>
                <button class="btn pequeno secundario">Enviar reseña</button>
            </form>
        <?php elseif ($p['resena_id']): ?>
            <p class="meta">✅ Ya calificaste este pedido.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
