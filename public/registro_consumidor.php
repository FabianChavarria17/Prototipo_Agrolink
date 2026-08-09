<?php
require_once __DIR__ . '/../includes/auth.php';

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($nombre === '' || $telefono === '' || $email === '' || $password === '') {
        $errores[] = 'Todos los campos marcados con * son obligatorios.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if (!$errores) {
        $chk = getDB()->prepare('SELECT id FROM usuarios WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        }
    }

    if (!$errores) {
        $stmt = getDB()->prepare(
            'INSERT INTO usuarios (tipo, nombre, telefono, email, password_hash, ubicacion)
             VALUES ("consumidor", ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $telefono, $email, password_hash($password, PASSWORD_DEFAULT), $ubicacion]);
        $_SESSION['usuario_id'] = getDB()->lastInsertId();
        setFlash('exito', '¡Cuenta creada! Bienvenido a AgroLink, ' . $nombre . '.');
        header('Location: catalogo.php');
        exit;
    }
}

$angosto = true;
$tituloPagina = 'Registro de consumidor';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Crear cuenta de consumidor</h1>
<p class="meta">Compra productos frescos directo del agricultor.</p>

<?php foreach ($errores as $e): ?>
    <div class="flash error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card">
    <form method="post" novalidate>
        <div class="form-group">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Teléfono *</label>
            <input type="text" name="telefono" required value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Correo electrónico *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Ubicación (cantón / distrito)</label>
            <input type="text" name="ubicacion" placeholder="Ej. La Unión, Cartago" value="<?= htmlspecialchars($_POST['ubicacion'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Contraseña *</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <button type="submit" class="btn ancho">Registrarme</button>
    </form>
</div>
<p class="meta">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
