<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';

// Solo los administradores pueden gestionar categorías.
if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";

// --- LÓGICA PARA REGISTRAR UNA NUEVA CATEGORÍA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_categoria'])) {
    $nombre = $_POST["nombre"] ?? '';
    $descripcion = $_POST["descripcion"] ?? '';

    if (empty($nombre)) {
        $mensaje = "⚠️ El nombre de la categoría es obligatorio.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM categorias_producto WHERE nombre = :nombre");
        $check_stmt->execute([':nombre' => $nombre]);

        if ($check_stmt->rowCount() > 0) {
            $mensaje = "⚠️ Ya existe una categoría con ese nombre.";
        } else {
            $sql = "INSERT INTO categorias_producto (nombre, descripcion) VALUES (:nombre, :descripcion)";
            $insert_stmt = $conn->prepare($sql);
            $insert_stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
            $mensaje = "✅ Categoría registrada correctamente.";
        }
    }
}

// Mensajes de éxito desde URL
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] == 'exito_eliminar') $mensaje = "✅ Categoría eliminada correctamente.";
    if ($_GET['mensaje'] == 'exito_editar') $mensaje = "✅ Categoría actualizada correctamente.";
}

// --- OBTENER LA LISTA DE CATEGORÍAS EXISTENTES ---
$categorias = $conn->query("SELECT id, nombre, descripcion FROM categorias_producto ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIBO - Gestión de Categorías</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .gestion-container {
            display: flex; flex-wrap: wrap; gap: 40px; margin-top: 20px;
        }
        .form-section, .list-section {
            background-color: #00224480;
            border: 2px solid rgba(255,255,255,0.18);
            padding: 30px; border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            color: rgba(255, 255, 255, 0.9);
        }
        .form-section { flex: 1; min-width: 350px; }
        .list-section { flex: 1.5; min-width: 450px; }
        h2 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { text-align: left; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .data-table th { background-color: rgba(0,0,0,0.2); }
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.712); padding: 11px 20px; text-decoration: none; border-radius: 5px; }
        .actions a { margin-right: 10px; text-decoration: none; color: #87cefa; font-weight: bold; }
        .actions a.delete { color: #ff8a8a; }
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 15px; color: #fff; font-weight: bold; }
        .alert-message.success { background-color: #28a745; }
        .alert-message.error { background-color: #dc3545; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Gestión de Categorías de Productos</h1>
            <p>Añada o administre las categorías principales del inventario.</p>
        </header>

        <div class="gestion-container">
            <div class="form-section">
                <h2>Registrar Nueva Categoría</h2>
                <?php if ($mensaje): ?>
                    <p class="alert-message <?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>"><?php echo $mensaje; ?></p>
                <?php endif; ?>

                <form action="gestion_categorias.php" method="POST">
                    <div class="input-group">
                        <label for="nombre">Nombre de la Categoría</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="input-group">
                        <label for="descripcion">Descripción (Opcional)</label>
                        <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                    </div>
                    <button type="submit" name="registrar_categoria" class="btn-login">Registrar Categoría</button>

                    <br><br>
                    <a href="dashboard.php" class="btn-secondary">Volver</a>
                </form>
            </div>

            <div class="list-section">
                <h2>Categorías Registradas</h2>
                <table class="data-table">
                    <thead>
                        <tr><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                            <td class="actions">
                                <a href="editar_categoria.php?id=<?php echo $categoria['id']; ?>">Editar</a>
                                <a href="gestion_categorias.php?eliminar_id=<?php echo $categoria['id']; ?>" class="delete" onclick="return confirm('¿Está seguro de que desea eliminar esta categoría? Solo se puede eliminar si no tiene productos asociados.');">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>