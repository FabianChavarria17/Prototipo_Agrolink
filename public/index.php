<?php
$tituloPagina = 'Inicio';
require_once __DIR__ . '/../includes/header.php';
$u = usuarioActual();
?>
<div class="hero">
    <img src="assets/img/logo.png" alt="AgroLink">
    <h1>Del campo a tu mesa, sin intermediarios</h1>
    <p>AgroLink™ conecta directamente a agricultores costarricenses con consumidores finales,
       eliminando intermediarios y devolviéndole al productor el control sobre el precio de su cosecha.</p>
    <?php if (!$u): ?>
        <div class="hero-botones">
            <a href="registro_agricultor.php" class="btn">Soy agricultor</a>
            <a href="registro_consumidor.php" class="btn secundario">Soy consumidor</a>
        </div>
    <?php elseif ($u['tipo'] === 'agricultor'): ?>
        <div class="hero-botones">
            <a href="productos_publicar.php" class="btn">Publicar un producto</a>
            <a href="productos_mios.php" class="btn secundario">Ver mi inventario</a>
        </div>
    <?php else: ?>
        <div class="hero-botones">
            <a href="catalogo.php" class="btn">Explorar catálogo</a>
        </div>
    <?php endif; ?>
</div>

<div class="grid">
    <div class="card">
        <h3>🌱 Sin intermediarios</h3>
        <p class="meta">El agricultor fija su propio precio y recibe el pago directo.</p>
    </div>
    <div class="card">
        <h3>📍 Cercanía real</h3>
        <p class="meta">Encuentra productos frescos según tu ubicación y zona de cobertura.</p>
    </div>
    <div class="card">
        <h3>🔒 Pago en custodia</h3>
        <p class="meta">El dinero se libera al productor solo cuando confirmas la entrega.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
