<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';

if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";
$producto_id = $_GET['id'] ?? null;

if (!$producto_id || !is_numeric($producto_id)) {
    header('Location: gestion_productos.php');
    exit();
}

// --- LÓGICA PARA ACTUALIZAR EL PRODUCTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger todos los datos del formulario
    $categoria_id = $_POST['categoria_id']; // Necesario para la redirección
    $codigo_profit = $_POST['codigo_profit'] ?? null;
    $descripcion = $_POST['descripcion'] ?? '';
    $aleacion = $_POST['aleacion'] ?? null;
    $libras_schedule = $_POST['libras_schedule'] ?? null;
    $ubicacion = $_POST['ubicacion'] ?? null;
    $stock = $_POST['stock'] ?? 0;
    $colada = $_POST['colada'] ?? null;
    $grado = $_POST['grado'] ?? null;
    $espesor = $_POST['espesor'] ?? null;
    $angulo = $_POST['angulo'] ?? null;

    $sql = "UPDATE productos SET 
                codigo_profit = :c_profit, descripcion = :desc, aleacion = :al, 
                libras_schedule = :ls, ubicacion = :ubi, stock = :stk, 
                colada = :col, grado = :gra, espesor = :esp, angulo = :ang
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':c_profit' => $codigo_profit, ':desc' => $descripcion, ':al' => $aleacion,
        ':ls' => $libras_schedule, ':ubi' => $ubicacion, ':stk' => $stock,
        ':col' => $colada, ':gra' => $grado, ':esp' => $espesor, ':ang' => $angulo,
        ':id' => $producto_id
    ]);
    
    header("Location: gestion_productos.php?categoria_id=" . $categoria_id . "&success=3");
    exit();
}

// --- OBTENER DATOS ACTUALES PARA EL FORMULARIO ---
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header('Location: gestion_productos.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .form-container {
            background-color: #00224480; border:2px solid rgba(255,255,255,0.18);
            padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px; margin: 20px auto;
        }
        h2 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { color: white; }
        .form-actions { margin-top: 30px; grid-column: 1 / -1; } /* Abarca todas las columnas */
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.9); padding: 11px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;}
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header">
            <h1>Editar Producto</h1>
        </header>

        <div class="form-container">
            <h2>Editando: <?php echo htmlspecialchars($producto['descripcion']); ?></h2>
            <form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="POST">
                <input type="hidden" name="categoria_id" value="<?php echo $producto['categoria_id']; ?>">
                <div class="form-grid">
                    <div class="input-group"><label>Descripción</label><input type="text" name="descripcion" value="<?php echo htmlspecialchars($producto['descripcion']); ?>" required></div>
                    <div class="input-group"><label>Código Profit</label><input type="text" name="codigo_profit" value="<?php echo htmlspecialchars($producto['codigo_profit']); ?>"></div>
                    <div class="input-group"><label>Stock Actual</label><input type="number" name="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" required></div>
                    <div class="input-group"><label>Ubicación</label><input type="text" name="ubicacion" value="<?php echo htmlspecialchars($producto['ubicacion']); ?>"></div>
                    <div class="input-group"><label>Aleación</label><input type="text" name="aleacion" value="<?php echo htmlspecialchars($producto['aleacion']); ?>"></div>
                    <div class="input-group"><label>Libras/Schedule</label><input type="text" name="libras_schedule" value="<?php echo htmlspecialchars($producto['libras_schedule']); ?>"></div>
                    <div class="input-group"><label>Colada</label><input type="text" name="colada" value="<?php echo htmlspecialchars($producto['colada']); ?>"></div>
                    <div class="input-group"><label>Grado</label><input type="text" name="grado" value="<?php echo htmlspecialchars($producto['grado']); ?>"></div>
                    <div class="input-group"><label>Espesor</label><input type="text" name="espesor" value="<?php echo htmlspecialchars($producto['espesor']); ?>"></div>
                    <div class="input-group"><label>Ángulo</label><input type="text" name="angulo" value="<?php echo htmlspecialchars($producto['angulo']); ?>"></div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-login">Actualizar Producto</button>
                        <br><br><br>
                        <a href="gestion_productos.php?categoria_id=<?php echo $producto['categoria_id']; ?>" class="btn-secondary">Volver</a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>