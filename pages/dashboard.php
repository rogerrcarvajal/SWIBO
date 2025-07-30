<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../src/db.php';

// --- NUEVA LÓGICA PARA EL DASHBOARD ---
// 1. Contar total de productos
$total_productos = $conn->query("SELECT COUNT(*) FROM productos")->fetchColumn();

// 2. Contar total de categorías
$total_categorias = $conn->query("SELECT COUNT(*) FROM categorias_producto")->fetchColumn();

// 3. Obtener productos con stock bajo
$productos_stock_bajo = $conn->query("
    SELECT codigo_profit, descripcion, stock, stock_minimo 
    FROM productos 
    WHERE stock <= stock_minimo AND stock_minimo > 0
    ORDER BY descripcion ASC
")->fetchAll(PDO::FETCH_ASSOC);
$count_stock_bajo = count($productos_stock_bajo);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .widget-list { color: white; list-style: none; padding: 0; margin-top: 10px; max-height: 150px; overflow-y: auto; }
        .widget-list li { padding: 5px; border-bottom: 1px solid #eee; font-size: 14px; }
        .widget-list span { float: right; font-weight: bold; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>
    <main class="main-content">
        <header class="main-header">
            <h1>Dashboard</h1>
            <p>Resumen general del estado del inventario.</p>
        </header>
        
        <div class="dashboard-widgets">
            <div class="widget">
                <h3>Total de Productos</h3>
                <p class="widget-data"><?php echo $total_productos; ?></p>
                <small>Productos distintos registrados</small>
            </div>
            <div class="widget">
                <h3>Categorías Activas</h3>
                <p class="widget-data"><?php echo $total_categorias; ?></p>
                <small>Tipos de producto gestionados</small>
            </div>
            <div class="widget <?php echo ($count_stock_bajo > 0) ? 'alert' : ''; ?>">
                <h3>Alertas de Stock Bajo</h3>
                <p class="widget-data"><?php echo $count_stock_bajo; ?></p>
                <small>Productos con existencias críticas</small>
                
                <?php if ($count_stock_bajo > 0): ?>
                    <ul class="widget-list">
                        <?php foreach($productos_stock_bajo as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                                <span><?php echo $item['stock']; ?> / <?php echo $item['stock_minimo']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            </div>
    </main>
</div>
</body>
</html>