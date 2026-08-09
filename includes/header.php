<?php
require_once __DIR__ . '/auth.php';
$u = usuarioActual();
$flash = tomarFlash();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' · ' : '' ?>AgroLink™</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/main.js" defer></script>
</head>
<body>
<div class="navbar">
    <a href="index.php" class="brand">
        <img src="assets/img/logo.png" alt="AgroLink">
        AgroLink™
    </a>
    <nav>
        <?php if ($u && $u['tipo'] === 'agricultor'): ?>
            <a href="productos_mios.php" class="<?= $paginaActual==='productos_mios.php'?'activo':'' ?>">Mi inventario</a>
            <a href="productos_publicar.php" class="<?= $paginaActual==='productos_publicar.php'?'activo':'' ?>">Publicar</a>
            <a href="pedidos_agricultor.php" class="<?= $paginaActual==='pedidos_agricultor.php'?'activo':'' ?>">Pedidos recibidos</a>
        <?php elseif ($u && $u['tipo'] === 'consumidor'): ?>
            <a href="catalogo.php" class="<?= $paginaActual==='catalogo.php'?'activo':'' ?>">Catálogo</a>
            <a href="carrito.php" class="<?= $paginaActual==='carrito.php'?'activo':'' ?>">Carrito
                <?php if (!empty($_SESSION['carrito'])): ?><span class="badge-noti"><?= count($_SESSION['carrito']) ?></span><?php endif; ?>
            </a>
            <a href="pedidos_consumidor.php" class="<?= $paginaActual==='pedidos_consumidor.php'?'activo':'' ?>">Mis pedidos</a>
        <?php endif; ?>

        <?php if ($u): ?>
            <a href="notificaciones.php" class="<?= $paginaActual==='notificaciones.php'?'activo':'' ?>">
                Notificaciones
                <?php $n = contarNotificacionesNoLeidas($u['id']); if ($n > 0): ?><span class="badge-noti"><?= $n ?></span><?php endif; ?>
            </a>
            <a href="perfil.php" class="<?= $paginaActual==='perfil.php'?'activo':'' ?>"><?= htmlspecialchars($u['nombre']) ?></a>
            <a href="logout.php">Salir</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
            <a href="registro_agricultor.php" class="btn pequeno">Soy agricultor</a>
            <a href="registro_consumidor.php" class="btn secundario pequeno">Soy consumidor</a>
        <?php endif; ?>
    </nav>
</div>
<div class="container<?= isset($angosto) && $angosto ? ' angosto' : '' ?>">
<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['mensaje']) ?></div>
<?php endif; ?>
