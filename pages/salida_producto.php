<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../db.php';

// Establecer la zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";

// --- LÓGICA PARA REGISTRAR LA SALIDA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_salida'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $nota_entrega = $_POST['numero_nota_entrega'];
    $fecha = $_POST['fecha_salida'];
    $usuario_id = $_SESSION['usuario_id'];
    $doc_referencia = "NE: $nota_entrega";

    $conn->beginTransaction();
    try {
        // 1. Obtener el stock actual y bloquear la fila para evitar concurrencia
        $stmt_stock = $conn->prepare("SELECT stock FROM productos WHERE id = :producto_id FOR UPDATE");
        $stmt_stock->execute([':producto_id' => $producto_id]);
        $stock_actual = $stmt_stock->fetchColumn();

        // 2. Validar que hay stock suficiente
        if ($stock_actual < $cantidad) {
            throw new Exception("Stock insuficiente. Stock actual: $stock_actual, se intentó sacar: $cantidad.");
        }

        // 3. Actualizar el stock del producto
        $sql_update = "UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':cantidad' => $cantidad, ':producto_id' => $producto_id]);

        // 4. Insertar el registro del movimiento
        $sql_insert = "INSERT INTO movimientos_inventario (producto_id, usuario_id, tipo, cantidad, doc_referencia, fecha_movimiento) 
                       VALUES (:pid, :uid, 'salida', :cant, :doc, :fecha)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([':pid' => $producto_id, ':uid' => $usuario_id, ':cant' => $cantidad, ':doc' => $doc_referencia, ':fecha' => $fecha]);

        $conn->commit();
        $mensaje = "✅ Salida de producto registrada correctamente.";

    } catch (Exception $e) {
        $conn->rollBack();
        $mensaje = "⚠️ Error al registrar la salida: " . $e->getMessage();
    }
}

$productos = $conn->query("SELECT id, descripcion, codigo_profit, stock FROM productos ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Productos - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .main-content { padding: 20px 40px; }
        .form-container {
            background-color: #00224480; border:2px solid rgba(255,255,255,0.18);
            padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px; margin: 20px auto;
        }
        h2 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        label { color: white; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.712); padding: 11px 20px; text-decoration: none; border-radius: 5px; }
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #fff; font-weight: bold; }
        .alert-message.success { background-color: #28a745; }
        .alert-message.error { background-color: #dc3545; }
        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single { height: 45px; background-color: #fff; border: 1px solid #ccc; border-radius: 5px; padding: 8px;}
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 43px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header"><h1>Salida de Productos del Inventario</h1></header>
        <div class="form-container">
            <h2>Registrar Nueva Salida</h2>
            <?php if ($mensaje): ?>
                <p class="alert-message <?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>"><?php echo $mensaje; ?></p>
            <?php endif; ?>
            <form action="salida_producto.php" method="POST">
                <div class="input-group full-width">
                    <label for="producto_id">Producto</label>
                    <select id="producto_id" name="producto_id" class="product-selector" required>
                        <option value="">-- Buscar y seleccionar un producto --</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>">
                                <?php echo htmlspecialchars($producto['descripcion']) . " (Cód: " . htmlspecialchars($producto['codigo_profit']) . " | Stock: " . $producto['stock'] . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="input-group"><label>Cantidad a Egresar</label><input type="number" name="cantidad" min="1" required></div>
                    <div class="input-group"><label>Fecha de Salida</label><input type="date" name="fecha_salida" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="input-group full-width"><label>Nº de Nota de Entrega</label><input type="text" name="numero_nota_entrega"></div>
                </div>
                <button type="submit" name="registrar_salida" class="btn-login" style="margin-top: 20px;">Registrar Salida</button>
            
                <br><br><br>
                <a href="dashboard.php" class="btn-secondary">Volver</a>            
            </form>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script> $(document).ready(function() { $('.product-selector').select2(); }); </script>
</body>
</html>