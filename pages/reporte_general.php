<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';
date_default_timezone_set('America/Caracas');

// Esta consulta compleja obtiene cada producto y lo une con su último movimiento registrado.
$sql = "
    SELECT
        p.codigo_profit,
        cp.nombre AS categoria_nombre,
        p.descripcion,
        p.ubicacion,
        p.stock,
        lm.fecha_movimiento,
        lm.tipo,
        lm.cantidad,
        lm.doc_referencia,
        u.nombre AS usuario_nombre
    FROM
        productos p
    JOIN
        categorias_producto cp ON p.categoria_id = cp.id
    LEFT JOIN LATERAL (
        SELECT *
        FROM movimientos_inventario mi
        WHERE mi.producto_id = p.id
        ORDER BY mi.fecha_movimiento DESC, mi.created_at DESC
        LIMIT 1
    ) lm ON true
    LEFT JOIN
        usuarios u ON lm.usuario_id = u.id
    ORDER BY
        cp.nombre, p.descripcion
";

$reporte_data = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Inventario - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .report-container {
            background-color: #00224480; border:2px solid rgba(255,255,255,0.18);
            padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            color: rgba(255, 255, 255, 0.9);
        }
        h2 { color: rgba(255, 255, 255, 0.9); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .data-table { width: 100%; color: white; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        .data-table th, .data-table td { text-align: left; padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .data-table th { background-color: rgba(0,0,0,0.2); }
        .entrada { color: #28a745; font-weight: bold; }
        .salida { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header">
            <h1>Reporte General de Inventario</h1>
            <p>Listado de productos con su último movimiento registrado. Fecha del reporte: <?php echo date('d/m/Y H:i'); ?></p>
            <br><br>
            <div style="margin-bottom: 20px;">
                <a href="generar_pdf_reporte_general.php" target="_blank" class="btn-login" style="text-decoration: none;">Generar PDF</a>
            </div>        
        </header>

        <div class="report-container">
            <h2>Listado General de Productos</h2>
            <div style="max-height: 70vh; overflow: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Ubicación</th>
                            <th colspan="5" style="text-align:center; background-color: rgba(0,0,0,0.4);">Último Movimiento (Kardex)</th>
                            <th>Stock Actual</th>
                        </tr>
                        <tr>
                            <th></th><th></th><th></th><th></th>
                            <th style="background-color: rgba(0,0,0,0.3);">Fecha</th>
                            <th style="background-color: rgba(0,0,0,0.3);">Tipo</th>
                            <th style="background-color: rgba(0,0,0,0.3);">Cant.</th>
                            <th style="background-color: rgba(0,0,0,0.3);">Doc. Ref.</th>
                            <th style="background-color: rgba(0,0,0,0.3);">Usuario</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reporte_data as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['codigo_profit']); ?></td>
                                <td><?php echo htmlspecialchars($item['categoria_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($item['ubicacion']); ?></td>
                                <td><?php echo $item['fecha_movimiento'] ? date('d/m/Y', strtotime($item['fecha_movimiento'])) : 'N/A'; ?></td>
                                <td class="<?php echo $item['tipo']; ?>"><?php echo $item['tipo'] ? strtoupper($item['tipo']) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($item['doc_referencia']); ?></td>
                                <td><?php echo htmlspecialchars($item['usuario_nombre']); ?></td>
                                <td style="font-weight: bold;"><?php echo $item['stock']; ?></td>
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