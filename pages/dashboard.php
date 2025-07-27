<?php
// ¡Línea más importante! Incluye el protector al inicio de todo.
require_once __DIR__ . '/../src/protector.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIBO - Dashboard</title>
    <link rel="stylesheet" href="../public/css/style.css">
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
            </div>
        </main>
    </div>
</body>
</html>