<?php
session_start();

// --- Protección de la Página ---
// Si no hay una sesión activa, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

// Obtener datos de la sesión
$nombre_usuario = $_SESSION['usuario_nombre'];
$rol_usuario = $_SESSION['usuario_rol'];
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
        <nav class="main-nav">
            <div class="nav-logo">
                <img src="../public/img/logo.png" alt="Logo Bix Oil">
            </div>
            <ul class="nav-menu">
                <li class="active"><a href="dashboard.php">Inicio</a></li>
                <li class="has-submenu">
                    <a href="#">Inventario</a>
                    <ul class="submenu">
                        <li><a href="#">Gestión de Categorías</a></li>
                        <li><a href="#">Gestión de Productos</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#">Control de Inventario</a>
                    <ul class="submenu">
                        <li><a href="#">Entrada de Productos</a></li>
                        <li><a href="#">Salida de Productos</a></li>
                        <li><a href="#">Kardex de Producto</a></li>
                    </ul>
                </li>
                <li><a href="#">Reportes</a></li>
                <?php if ($rol_usuario == 'Admin'): ?>
                    <li><a href="#">Mantenimiento</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-user">
                <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <a href="logout.php" class="btn-logout">Salir</a>
            </div>
        </nav>

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