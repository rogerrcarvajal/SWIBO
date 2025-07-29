<?php
// src/templates/navbar.php

// Este bloque se asegura de que la sesión esté iniciada.
// Si este archivo es incluido desde una página que ya inició la sesión (como debe ser),
// no hará nada. Si se intenta acceder directamente, iniciará una nueva.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteger el acceso directo y verificar datos de sesión
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay sesión, no mostrar nada o redirigir.
    // Una redirección es más segura.
    header('Location: /swibo/index.php'); // Ajusta la ruta a tu proyecto
    exit();
}

// Obtener datos del usuario para el menú
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol_usuario = $_SESSION['usuario_rol'] ?? 'Consulta';

?>

<nav class="main-nav">
    <div class="nav-logo">
        <img src="/swibo/public/img/logo.png" alt="Logo Bix Oil"> </div>
    <ul class="nav-menu">
        <li class="active"><a href="dashboard.php">Inicio</a></li>
        <li class="has-submenu">
            <a href="#">Inventario</a>
            <ul class="submenu">
                <li><a href="/swibo/pages/gestion_categorias.php">Gestionar Categorías</a></li>
                <li><a href="/swibo/pages/gestion_productos.php">Gestionar Productos</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="#">Control de Inventario</a>
            <ul class="submenu">
                <li><a href="/swibo/pages/entrada_producto.php">Entrada de Productos</a></li>
                <li><a href="/swibo/pages/salida_producto.php">Salida de Productos</a></li>
                <li><a href="/swibo/pages/kardex_producto.php">Kardex de Producto</a></li>
            </ul>
        </li>
        <li><a href="/swibo/pages/reporte_general.php">Reportes</a></li>
        
        <?php if ($rol_usuario == 'Admin'): ?>
            <li class="has-submenu">
            <a href="#">Mantenimiento</a>
            <ul class="submenu">
                <li><a href="/swibo/pages/gestion_usuarios.php">Gestion de Usuarios</a></li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
    <div class="nav-user">
        <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
        <a href="logout.php" class="btn-logout">Salir</a> </div>
</nav>