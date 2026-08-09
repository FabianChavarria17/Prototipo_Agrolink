<?php
require_once __DIR__ . '/../includes/auth.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDB()->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        $_SESSION['usuario_id'] = $u['id'];
        header('Location: ' . ($u['tipo'] === 'agricultor' ? 'productos_mios.php' : 'catalogo.php'));
        exit;
    }
    $error = 'Correo o contraseña incorrectos.';
}

$angosto = true;
$tituloPagina = 'Iniciar sesión';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Iniciar sesión</h1>

<?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
    <form method="post">
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn ancho">Entrar</button>
    </form>
    <p class="meta" style="margin-top:12px;"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
</div>

<p class="meta">
    ¿No tienes cuenta?
    <a href="registro_agricultor.php">Regístrate como agricultor</a> o
    <a href="registro_consumidor.php">como consumidor</a>.
</p>
<div class="card" style="background:var(--verde-claro); border:none;">
    <strong>Cuentas de demostración</strong> (contraseña: <code>agrolink123</code>)
    <ul style="margin:8px 0 0; padding-left:18px; font-size:.88rem;">
        <li>Agricultor: hector@agrolink.test</li>
        <li>Consumidor: fabian@agrolink.test</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
