<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('agricultor');
$u = usuarioActual();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int) $_POST['pedido_id'];
    $accion = $_POST['accion'] ?? '';

    $stmt = $db->prepare('SELECT * FROM pedidos WHERE id = ? AND agricultor_id = ?');
    $stmt->execute([$pedidoId, $u['id']]);
    $pedido = $stmt->fetch();

    if ($pedido) {
        $mapaEstados = [
            'aceptar'   => 'aceptado',
            'rechazar'  => 'rechazado',
            'despachar' => 'en_camino',
        ];
        if (isset($mapaEstados[$accion])) {
            $nuevoEstado = $mapaEstados[$accion];
            $db->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$nuevoEstado, $pedidoId]);

            // HU-14: disparadores de notificación al consumidor según evento del ciclo de vida.
            $mensajes = [
                'aceptado'  => 'Tu pedido #' . $pedidoId . ' fue aceptado por el agricultor.',
                'rechazado' => 'Tu pedido #' . $pedidoId . ' fue rechazado por el agricultor.',
                'en_camino' => 'Tu pedido #' . $pedidoId . ' fue despachado y va en camino.',
            ];
            crearNotificacion($pedido['consumidor_id'], $mensajes[$nuevoEstado]);
            setFlash('exito', 'Pedido actualizado.');
        }
    }
    header('Location: pedidos_agricultor.php');
    exit;
}

$stmt = $db->prepare(
    'SELECT pe.*, c.nombre AS consumidor_nombre
     FROM pedidos pe JOIN usuarios c ON c.id = pe.consumidor_id
     WHERE pe.agricultor_id = ? ORDER BY pe.creado_en DESC'
);
$stmt->execute([$u['id']]);
$pedidos = $stmt->fetchAll();

$etiquetas = [
    'pendiente' => ['Pendiente', 'amarillo'],
    'aceptado' => ['En preparación', 'verde'],
    'en_camino' => ['En camino', 'verde'],
    'entregado' => ['Entregado', 'gris'],
    'rechazado' => ['Rechazado', 'rojo'],
    'pago_liberado' => ['Pago liberado', 'gris'],
];

$tituloPagina = 'Pedidos recibidos';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Pedidos recibidos</h1>

<?php if (!$pedidos): ?>
    <div class="card empty-state">Aún no has recibido pedidos.</div>
<?php else: ?>
<div class="card">
<table>
    <tr><th>#</th><th>Consumidor</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
    <?php foreach ($pedidos as $p): [$etiqueta, $color] = $etiquetas[$p['estado']] ?? [$p['estado'], 'gris']; ?>
    <tr>
        <td>#<?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['consumidor_nombre']) ?></td>
        <td><?= formatoColones($p['total_crc']) ?></td>
        <td><span class="tag <?= $color ?>"><?= $etiqueta ?></span></td>
        <td>
            <?php if ($p['estado'] === 'pendiente'): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="aceptar">
                    <button class="btn pequeno">Aceptar</button>
                </form>
                <form method="post" style="display:inline;" data-confirm="¿Rechazar el pedido #<?= $p['id'] ?>? Esta acción no se puede deshacer.">
                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <button class="btn pequeno peligro">Rechazar</button>
                </form>
            <?php elseif ($p['estado'] === 'aceptado'): ?>
                <form method="post">
                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="despachar">
                    <button class="btn pequeno">Marcar en camino</button>
                </form>
            <?php else: ?>
                <span class="meta">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
