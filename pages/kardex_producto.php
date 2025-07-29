<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';

$selected_producto_id = $_GET['producto_id'] ?? null;
$movimientos = [];
$producto_seleccionado = null;

// Obtener todos los productos para el selector
$productos = $conn->query("SELECT id, descripcion, codigo_profit, stock FROM productos ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($selected_producto_id) {
    // Obtener info del producto seleccionado
    $stmt_prod = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt_prod->execute([$selected_producto_id]);
    $producto_seleccionado = $stmt_prod->fetch(PDO::FETCH_ASSOC);

    // Obtener todos los movimientos de ese producto
    $stmt_mov = $conn->prepare("
        SELECT m.*, u.nombre as usuario_nombre 
        FROM movimientos_inventario m
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        WHERE m.producto_id = ? 
        ORDER BY m.fecha_movimiento DESC, m.created_at DESC
    ");
    $stmt_mov->execute([$selected_producto_id]);
    $movimientos = $stmt_mov->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex de Producto - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .main-content { padding: 20px 40px; }
        .selector-container, .results-container {
            background-color: #00224480; border:2px solid rgba(255,255,255,0.18);
            padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px auto;
        }
        h2, h3 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        label { color: white; }
        .data-table { width: 100%; color: white; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { text-align: left; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .data-table th { background-color: rgba(0,0,0,0.2); }
        .entrada { color: #28a745; font-weight: bold; }
        .salida { color: #dc3545; font-weight: bold; }
        .select2-container--default .select2-selection--single { height: 45px; background-color: #fff; border: 1px solid #ccc; border-radius: 5px; padding: 8px;}
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 43px; }
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.712); padding: 11px 20px; text-decoration: none; border-radius: 5px; }

    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header"><h1>Kardex de Producto (Historial de Movimientos)</h1></header>
        
        <div class="selector-container">
            <form action="kardex_producto.php" method="GET">
                <div class="input-group">
                    <label for="producto_id">Seleccione un Producto para ver su historial</label>
                    <select id="producto_id" name="producto_id" class="product-selector" onchange="this.form.submit()">
                        <option value="">-- Buscar y seleccionar --</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>" <?php echo ($selected_producto_id == $producto['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($producto['descripcion']) . " (Cód: " . htmlspecialchars($producto['codigo_profit']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br><br>
                    <a href="dashboard.php" class="btn-secondary">Volver</a>         
                </div>
            </form>
        </div>

        <?php if ($producto_seleccionado): ?>
            <div class="results-container">
                <h2>Historial de: <?php echo htmlspecialchars($producto_seleccionado['descripcion']); ?></h2>
                <form action="generar_pdf_kardex.php" method="GET" target="_blank" style="margin-bottom: 20px;">
                    <input type="hidden" name="producto_id" value="<?php echo $selected_producto_id; ?>">
                    <button type="submit" class="btn-login">Generar PDF</button>
                </form>
                <h3>Stock Actual: <?php echo htmlspecialchars($producto_seleccionado['stock']); ?> unidades</h3>
                <h3>Stock Actual: <?php echo htmlspecialchars($producto_seleccionado['stock']); ?> unidades</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Documento Ref.</th><th>Usuario</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($mov['fecha_movimiento'])); ?></td>
                                <td class="<?php echo $mov['tipo']; ?>"><?php echo strtoupper($mov['tipo']); ?></td>
                                <td><?php echo $mov['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($mov['doc_referencia']); ?></td>
                                <td><?php echo htmlspecialchars($mov['usuario_nombre']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script> $(document).ready(function() { $('.product-selector').select2(); }); </script>
</body>
</html>