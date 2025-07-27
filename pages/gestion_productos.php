<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';

// Solo los administradores pueden gestionar productos.
if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";
$selected_categoria_id = $_GET['categoria_id'] ?? null;
$productos = [];
$categoria_seleccionada = null;

// --- LÓGICA PARA REGISTRAR UN NUEVO PRODUCTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_producto'])) {
    // Recolectar datos del producto
    $categoria_id = $_POST['categoria_id'];
    $codigo_profit = $_POST['codigo_profit'];
    $descripcion = $_POST['descripcion'];
    $aleacion = $_POST['aleacion'];
    $libras_schedule = $_POST['libras_schedule'];
    $ubicacion = $_POST['ubicacion'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO productos (categoria_id, codigo_profit, descripcion, aleacion, libras_schedule, ubicacion, stock) 
            VALUES (:categoria_id, :codigo_profit, :descripcion, :aleacion, :libras_schedule, :ubicacion, :stock)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':categoria_id' => $categoria_id, ':codigo_profit' => $codigo_profit, ':descripcion' => $descripcion,
        ':aleacion' => $aleacion, ':libras_schedule' => $libras_schedule, ':ubicacion' => $ubicacion, ':stock' => $stock
    ]);
    
    // Redirigir para mostrar la lista actualizada y evitar reenvío del formulario
    header("Location: gestion_productos.php?categoria_id=" . $categoria_id . "&success=1");
    exit();
}

// Obtener todas las categorías para el menú desplegable
$categorias = $conn->query("SELECT id, nombre FROM categorias_producto ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Si se ha seleccionado una categoría, obtener sus productos y su nombre
if ($selected_categoria_id) {
    // Obtener el nombre de la categoría
    $stmt_cat = $conn->prepare("SELECT nombre FROM categorias_producto WHERE id = ?");
    $stmt_cat->execute([$selected_categoria_id]);
    $categoria_seleccionada = $stmt_cat->fetch();

    // Obtener productos de esa categoría
    $stmt_prod = $conn->prepare("SELECT * FROM productos WHERE categoria_id = ? ORDER BY descripcion ASC");
    $stmt_prod->execute([$selected_categoria_id]);
    $productos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
}

// Mensaje de éxito después de la redirección
if (isset($_GET['success'])) {
    $mensaje = "✅ Producto registrado correctamente.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .gestion-container { display: flex; flex-wrap: wrap; gap: 40px; margin-top: 20px; }
        .form-section, .list-section { background-color: #00224480; border: 2px solid rgba(255,255,255,0.18); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: rgba(255, 255, 255, 0.9); }
        .form-section { flex: 1; min-width: 350px; }
        .list-section { flex: 2; min-width: 500px; }
        h2, h3 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { text-align: left; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .data-table th { background-color: rgba(0,0,0,0.2); }
        .actions a { margin-right: 10px; text-decoration: none; color: #87cefa; font-weight: bold; }
        .actions a.delete { color: #ff8a8a; }
        .selector-container { background-color: #00224480; border: 2px solid rgba(255,255,255,0.18); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 15px; color: #fff; font-weight: bold; background-color: #28a745; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Gestión de Productos por Categoría</h1>
            <p>Seleccione una categoría para empezar a registrar productos.</p>
        </header>

        <div class="selector-container">
            <form action="gestion_productos.php" method="GET">
                <div class="input-group">
                    <label for="categoria_id" style="color: white; font-weight: bold;">Seleccionar Categoría:</label>
                    <select id="categoria_id" name="categoria_id" onchange="this.form.submit()">
                        <option value="">-- Elija una categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($selected_categoria_id == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_categoria_id && $categoria_seleccionada): ?>
        <div class="gestion-container">
            <div class="form-section">
                <h3>Añadir Producto a: <?php echo htmlspecialchars($categoria_seleccionada['nombre']); ?></h3>
                <?php if ($mensaje): ?><p class="alert-message"><?php echo $mensaje; ?></p><?php endif; ?>
                
                <form action="gestion_productos.php" method="POST">
                    <input type="hidden" name="categoria_id" value="<?php echo $selected_categoria_id; ?>">
                    <div class="input-group"><label>Código Profit</label><input type="text" name="codigo_profit"></div>
                    <div class="input-group"><label>Descripción</label><input type="text" name="descripcion" required></div>
                    <div class="input-group"><label>Aleación</label><input type="text" name="aleacion"></div>
                    <div class="input-group"><label>Libras/Schedule</label><input type="text" name="libras_schedule"></div>
                    <div class="input-group"><label>Ubicación</label><input type="text" name="ubicacion"></div>
                    <div class="input-group"><label>Stock Inicial</label><input type="number" name="stock" value="0" required></div>
                    <button type="submit" name="registrar_producto" class="btn-login">Registrar Producto</button>
                </form>
            </div>

            <div class="list-section">
                <h3>Productos en: <?php echo htmlspecialchars($categoria_seleccionada['nombre']); ?></h3>
                <table class="data-table">
                    <thead><tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Ubicación</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['codigo_profit']); ?></td>
                            <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                            <td><?php echo htmlspecialchars($producto['ubicacion']); ?></td>
                            <td class="actions"><a href="#">Editar</a><a href="#" class="delete">Eliminar</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>