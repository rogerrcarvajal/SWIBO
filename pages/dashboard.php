<?php
session_start();

// --- Protección de la Página ---
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /swibo/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SWIBO</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        
        <?php require_once __DIR__ . '/swibo/src/templates/navbar.php'; ?>

        <main class="main-content">
            <header class="main-header">
                <h1>Dashboard</h1>
                <p>Resumen general del estado del inventario.</p>
            </header>
            
            <div class="dashboard-widgets">
                <div class="widget">
                    <h3>Total de Productos</h3>
                    <p class="widget-data">1,250</p>
                    <small>Productos distintos registrados</small>
                </div>
                <div class="widget">
                    <h3>Categorías Activas</h3>
                    <p class="widget-data">28</p>
                    <small>Tipos de producto gestionados</small>
                </div>
                <div class="widget">
                    <h3>Movimientos Hoy</h3>
                    <p class="widget-data">15 Entradas / 8 Salidas</p>
                    <small>Actualizado en tiempo real</small>
                </div>
                 <div class="widget alert">
                    <h3>Alertas de Stock Bajo</h3>
                    <p class="widget-data">5</p>
                    <small>Productos con existencias críticas</small>
                </div>
            </div>
        </main>
    </div>
</body>
</html>