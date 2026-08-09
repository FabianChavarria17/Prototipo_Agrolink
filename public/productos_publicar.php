<?php
require_once __DIR__ . '/../includes/auth.php';
requerirTipo('agricultor');
$u = usuarioActual();

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $categoria   = trim($_POST['categoria'] ?? '');
    $precio      = (float) ($_POST['precio_crc'] ?? 0);
    $cantidad    = (float) ($_POST['cantidad_kg'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagenNombre = null;

    if ($nombre === '' || $categoria === '') {
        $errores[] = 'Nombre y categoría son obligatorios.';
    }
    // HU-05 (refinada con IA): validación de casos borde — precio o stock no pueden ser cero/negativos.
    if ($precio <= 0) {
        $errores[] = 'El precio debe ser mayor a ₡0.';
    }
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad disponible debe ser mayor a 0 kg.';
    }

    // HU-07: carga y validación básica de imagen (JPG/PNG, máx 5MB)
    if (!empty($_FILES['imagen']['name'])) {
        $permitidos = ['image/jpeg', 'image/png'];
        $tipoArchivo = mime_content_type($_FILES['imagen']['tmp_name']);
        if (!in_array($tipoArchivo, $permitidos, true)) {
            $errores[] = 'La imagen debe ser JPG o PNG.';
        } elseif ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
            $errores[] = 'La imagen no puede superar 5 MB.';
        } else {
            $carpeta = __DIR__ . '/assets/img/productos/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            $ext = $tipoArchivo === 'image/png' ? 'png' : 'jpg';
            $imagenNombre = 'prod_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . $imagenNombre);
        }
    }

    if (!$errores) {
        $stmt = getDB()->prepare(
            'INSERT INTO productos (agricultor_id, nombre, categoria, precio_crc, cantidad_kg, imagen, descripcion)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$u['id'], $nombre, $categoria, $precio, $cantidad, $imagenNombre, $descripcion]);
        setFlash('exito', 'Producto publicado en el catálogo.');
        header('Location: productos_mios.php');
        exit;
    }
}

$tituloPagina = 'Publicar producto';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>Publicar producto</h1>

<?php foreach ($errores as $e): ?>
    <div class="flash error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px;">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nombre del producto *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" placeholder="Ej. Tomate orgánico">
        </div>
        <div class="form-group">
            <label>Categoría *</label>
            <select name="categoria" required>
                <option value="">Selecciona...</option>
                <?php foreach (['Verduras','Frutas','Granos','Hierbas','Lácteos','Otros'] as $c): ?>
                    <option value="<?= $c ?>" <?= (($_POST['categoria'] ?? '') === $c) ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Precio (₡ por kg) *</label>
            <input type="number" step="0.01" min="1" name="precio_crc" required data-min-mayor-cero value="<?= htmlspecialchars($_POST['precio_crc'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Cantidad disponible (kg) *</label>
            <input type="number" step="0.01" min="0.1" name="cantidad_kg" required data-min-mayor-cero value="<?= htmlspecialchars($_POST['cantidad_kg'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Fotografía (JPG/PNG, máx 5MB) — HU-07</label>
            <input type="file" name="imagen" accept="image/jpeg,image/png">
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="3" placeholder="Detalles de cosecha, método de cultivo, etc."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn ancho">Publicar cosecha</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
