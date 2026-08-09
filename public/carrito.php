<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('consumidor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar'])) {
    unset($_SESSION['carrito'][$_POST['quitar']]);
    header('Location: carrito.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
foreach ($carrito as $item) $total += $item['precio_crc'] * $item['cantidad_kg'];

$tituloPagina = 'Carrito';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Mi carrito</h1>

<?php if (!$carrito): ?>
    <div class="card empty-state">Tu carrito está vacío. <a href="catalogo.php">Explora el catálogo</a>.</div>
<?php else: ?>
<div class="card">
<table>
    <tr><th>Producto</th><th>Agricultor</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
    <?php foreach ($carrito as $id => $item): ?>
    <tr>
        <td><?= htmlspecialchars($item['nombre']) ?></td>
        <td><?= htmlspecialchars($item['agricultor_nombre']) ?></td>
        <td><?= number_format($item['cantidad_kg'], 1) ?> kg</td>
        <td><?= formatoColones($item['precio_crc'] * $item['cantidad_kg']) ?></td>
        <td>
            <form method="post" data-confirm="¿Quitar <?= htmlspecialchars($item['nombre'], ENT_QUOTES) ?> del carrito?">
                <input type="hidden" name="quitar" value="<?= $id ?>">
                <button type="submit" class="btn pequeno peligro">Quitar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<h2 style="text-align:right;">Total: <?= formatoColones($total) ?></h2>
<a href="checkout.php" class="btn ancho">Continuar al pago</a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
