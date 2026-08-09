<?php
require_once __DIR__ . '/../includes/auth.php';
requerirLogin();
$u = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono  = trim($_POST['telefono'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    $zona      = (int) ($_POST['zona_cobertura_km'] ?? 0);

    if ($u['tipo'] === 'agricultor') {
        $stmt = getDB()->prepare(
            'UPDATE usuarios SET telefono = ?, ubicacion = ?, bio = ?, zona_cobertura_km = ? WHERE id = ?'
        );
        $stmt->execute([$telefono, $ubicacion, $bio, $zona ?: null, $u['id']]);
    } else {
        $stmt = getDB()->prepare('UPDATE usuarios SET telefono = ?, ubicacion = ? WHERE id = ?');
        $stmt->execute([$telefono, $ubicacion, $u['id']]);
    }
    setFlash('exito', 'Perfil actualizado correctamente.');
    header('Location: perfil.php');
    exit;
}

$angosto = true;
$tituloPagina = 'Mi perfil';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Mi perfil</h1>

<div class="card">
    <form method="post">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" value="<?= htmlspecialchars($u['nombre']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Correo</label>
            <input type="text" value="<?= htmlspecialchars($u['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($u['telefono']) ?>" required>
        </div>
        <div class="form-group">
            <label>Ubicación</label>
            <input type="text" name="ubicacion" value="<?= htmlspecialchars($u['ubicacion'] ?? '') ?>">
        </div>
        <?php if ($u['tipo'] === 'agricultor'): ?>
            <div class="form-group">
                <label>Biografía pública (HU-16)</label>
                <textarea name="bio" rows="3"><?= htmlspecialchars($u['bio'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Zona de cobertura de entregas (km)</label>
                <input type="number" name="zona_cobertura_km" min="1" value="<?= htmlspecialchars($u['zona_cobertura_km'] ?? '') ?>">
            </div>
        <?php endif; ?>
        <button type="submit" class="btn ancho">Guardar cambios</button>
    </form>
</div>

<?php if ($u['tipo'] === 'agricultor'): ?>
    <p class="meta">Así te ven los consumidores: <a href="perfil_publico.php?id=<?= $u['id'] ?>">ver mi perfil público</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
