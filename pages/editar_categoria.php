<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../db.php';

if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";
$categoria_id = $_GET['id'] ?? null;

if (!$categoria_id || !is_numeric($categoria_id)) {
    header('Location: gestion_categorias.php');
    exit();
}

// --- LÓGICA PARA ACTUALIZAR LA CATEGORÍA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if (empty($nombre)) {
        $mensaje = "⚠️ El nombre no puede estar vacío.";
    } else {
        // Verificar que el nuevo nombre no exista ya en otra categoría
        $check_stmt = $conn->prepare("SELECT id FROM categorias_producto WHERE nombre = :nombre AND id != :id");
        $check_stmt->execute([':nombre' => $nombre, ':id' => $categoria_id]);

        if ($check_stmt->rowCount() > 0) {
            $mensaje = "⚠️ Ya existe otra categoría con ese nombre.";
        } else {
            $sql = "UPDATE categorias_producto SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
            $update_stmt = $conn->prepare($sql);
            $update_stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':id' => $categoria_id]);
            header("Location: gestion_categorias.php?mensaje=exito_editar");
            exit();
        }
    }
}

// --- OBTENER DATOS ACTUALES PARA EL FORMULARIO ---
$stmt = $conn->prepare("SELECT * FROM categorias_producto WHERE id = ?");
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header('Location: gestion_categorias.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .form-container {
            background-color: #00224480;
            border: 2px solid rgba(255,255,255,0.18);
            padding: 40px; border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 700px; margin: 20px auto;
        }
        h2 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .form-actions { margin-top: 20px; }
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.9); padding: 11px 20px; text-decoration: none; border-radius: 5px; }
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #fff; font-weight: bold; background-color: #dc3545; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header">
            <h1>Editar Categoría</h1>
        </header>

        <div class="form-container">
            <h2>Editando a "<?php echo htmlspecialchars($categoria['nombre']); ?>"</h2>
            <?php if ($mensaje): ?><p class="alert-message"><?php echo $mensaje; ?></p><?php endif; ?>
            
            <form action="editar_categoria.php?id=<?php echo $categoria['id']; ?>" method="POST">
                <div class="input-group">
                    <label style="color:white" for="nombre">Nombre de la Categoría</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($categoria['nombre']); ?>" required>
                </div>
                <div class="input-group">
                    <label style="color:white" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($categoria['descripcion']); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-login">Actualizar Categoría</button>
                    <br><br><br>
                    <a href="gestion_categorias.php" class="btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>