<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('consumidor');
$u = usuarioActual();
$db = getDB();

$carrito = $_SESSION['carrito'] ?? [];
if (!$carrito) { header('Location: carrito.php'); exit; }

$total = 0;
foreach ($carrito as $item) $total += $item['precio_crc'] * $item['cantidad_kg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo = $_POST['metodo_pago'] ?? '';
    if (!in_array($metodo, ['sinpe', 'tarjeta'], true)) {
        $error = 'Selecciona un método de pago.';
    } else {
        // Agrupar por agricultor: un pedido por agricultor presente en el carrito.
        $porAgricultor = [];
        foreach ($carrito as $item) $porAgricultor[$item['agricultor_id']][] = $item;

        $db->beginTransaction();
        try {
            foreach ($porAgricultor as $agricultorId => $items) {
                $totalPedido = 0;
                foreach ($items as $it) $totalPedido += $it['precio_crc'] * $it['cantidad_kg'];

                $stmt = $db->prepare(
                    'INSERT INTO pedidos (consumidor_id, agricultor_id, estado, total_crc, metodo_pago, pago_en_custodia)
                     VALUES (?, ?, "pendiente", ?, ?, 1)'
                );
                $stmt->execute([$u['id'], $agricultorId, $totalPedido, $metodo]);
                $pedidoId = $db->lastInsertId();

                foreach ($items as $it) {
                    $stmt = $db->prepare(
                        'INSERT INTO pedido_items (pedido_id, producto_id, cantidad_kg, precio_unitario_crc) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->execute([$pedidoId, $it['producto_id'], $it['cantidad_kg'], $it['precio_crc']]);

                    // Descontar del inventario del agricultor.
                    $upd = $db->prepare('UPDATE productos SET cantidad_kg = GREATEST(cantidad_kg - ?, 0) WHERE id = ?');
                    $upd->execute([$it['cantidad_kg'], $it['producto_id']]);
                }

                // HU-14: notificación al agricultor — evento "Orden recibida".
                crearNotificacion($agricultorId, 'Tienes un nuevo pedido #' . $pedidoId . ' de ' . $u['nombre'] . '.');
            }
            $db->commit();
            unset($_SESSION['carrito']);
            setFlash('exito', 'Pago procesado. El dinero queda en custodia hasta confirmar la entrega.');
            header('Location: pedidos_consumidor.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'No se pudo procesar el pago. Intenta de nuevo.';
        }
    }
}

$angosto = true;
$tituloPagina = 'Pago';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Confirmar y pagar</h1>

<?php if (isset($error)): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
    <h3>Resumen</h3>
    <?php foreach ($carrito as $item): ?>
        <div class="meta"><?= htmlspecialchars($item['nombre']) ?> — <?= number_format($item['cantidad_kg'],1) ?> kg — <?= formatoColones($item['precio_crc'] * $item['cantidad_kg']) ?></div>
    <?php endforeach; ?>
    <h2 style="text-align:right;">Total: <?= formatoColones($total) ?></h2>
</div>

<div class="card">
    <form method="post">
        <div class="form-group">
            <label>Método de pago</label>
            <select name="metodo_pago" required>
                <option value="">Selecciona...</option>
                <option value="sinpe">SINPE Móvil</option>
                <option value="tarjeta">Tarjeta de crédito/débito</option>
            </select>
        </div>
        <p class="meta">🔒 El monto se retiene en custodia (escrow) y solo se libera al agricultor cuando confirmes la recepción del pedido.</p>
        <button type="submit" class="btn ancho">Pagar <?= formatoColones($total) ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
