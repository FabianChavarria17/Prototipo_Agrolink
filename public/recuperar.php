<?php
require_once __DIR__ . '/../includes/auth.php';

$paso = $_GET['paso'] ?? '1';
$mensaje = null;
$error = null;
$otpDemo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso === '1') {
    $email = trim($_POST['email'] ?? '');
    $stmt = getDB()->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u) {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $upd = getDB()->prepare('UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?');
        $upd->execute([$otp, $expira, $u['id']]);
        // En producción esto se envía por correo/SMS; aquí se muestra en pantalla para el prototipo.
        $otpDemo = $otp;
        $_SESSION['recuperar_email'] = $email;
        $paso = '2';
    } else {
        $error = 'No existe una cuenta con ese correo.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso === '2') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nueva = $_POST['password'] ?? '';
    $email = $_SESSION['recuperar_email'] ?? '';

    $stmt = getDB()->prepare('SELECT * FROM usuarios WHERE email = ? AND reset_token = ? AND reset_token_expira >= NOW()');
    $stmt->execute([$email, $codigo]);
    $u = $stmt->fetch();

    if (!$u) {
        $error = 'Código inválido o expirado. Solicita uno nuevo.';
        $paso = '2';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        $paso = '2';
    } else {
        $upd = getDB()->prepare('UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_token_expira = NULL WHERE id = ?');
        $upd->execute([password_hash($nueva, PASSWORD_DEFAULT), $u['id']]);
        unset($_SESSION['recuperar_email']);
        // HU-17: cierra sesiones activas previas — en este prototipo no hay sesiones concurrentes que rastrear.
        setFlash('exito', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        header('Location: login.php');
        exit;
    }
}

$angosto = true;
$tituloPagina = 'Recuperar contraseña';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Recuperar contraseña</h1>

<?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($paso === '1'): ?>
    <div class="card">
        <p class="meta">Ingresa tu correo y te enviaremos un código de 6 dígitos (válido 15 minutos).</p>
        <form method="post">
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn ancho">Enviar código</button>
        </form>
    </div>
<?php else: ?>
    <?php if ($otpDemo): ?>
        <div class="flash exito">
            Código enviado (simulado). Para efectos del prototipo, tu código es: <strong><?= htmlspecialchars($otpDemo) ?></strong>
        </div>
    <?php endif; ?>
    <div class="card">
        <form method="post">
            <div class="form-group">
                <label>Código de verificación</label>
                <input type="text" name="codigo" maxlength="6" required autofocus>
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="btn ancho">Restablecer contraseña</button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
