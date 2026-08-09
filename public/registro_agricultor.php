<?php
require_once __DIR__ . '/../includes/auth.php';

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $cedula   = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $zona     = (int) ($_POST['zona_cobertura_km'] ?? 0);
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $cedula === '' || $telefono === '' || $email === '' || $password === '') {
        $errores[] = 'Todos los campos marcados con * son obligatorios.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if (!$errores) {
        $db = getDB();
        $chk = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        }
    }

    if (!$errores) {
        $stmt = getDB()->prepare(
            'INSERT INTO usuarios (tipo, nombre, cedula, telefono, email, password_hash, ubicacion, zona_cobertura_km)
             VALUES ("agricultor", ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $nombre, $cedula, $telefono, $email,
            password_hash($password, PASSWORD_DEFAULT),
            $ubicacion, $zona ?: null,
        ]);
        $_SESSION['usuario_id'] = getDB()->lastInsertId();
        setFlash('exito', '¡Cuenta creada! Bienvenido a AgroLink, ' . $nombre . '.');
        header('Location: index.php');
        exit;
    }
}

$angosto = true;
$tituloPagina = 'Registro de agricultor';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Crear cuenta de agricultor</h1>
<p class="meta">Publica tus cosechas y véndelas directo al consumidor.</p>

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
            <label>Cédula *</label>
            <input type="text" name="cedula" required value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>">
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
            <input type="text" name="ubicacion" placeholder="Ej. San Ramón, Alajuela" value="<?= htmlspecialchars($_POST['ubicacion'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Zona de cobertura de entregas (km) — HU-19</label>
            <input type="number" name="zona_cobertura_km" min="1" placeholder="Ej. 15" value="<?= htmlspecialchars($_POST['zona_cobertura_km'] ?? '') ?>">
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
