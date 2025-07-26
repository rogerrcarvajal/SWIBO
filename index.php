<?php
session_start();
// Si ya hay una sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIBO - Login</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="public/img/logo.png" alt="Logo Bix Oil" class="logo">
            <h1>Sistema Web para el<br>Control de Inventario<br></h1>
            
            <?php if (isset($_GET['error'])): ?>
                <p class="error-message">Usuario o contraseña incorrectos.</p>
            <?php endif; ?>

            <form action="api/auth.php" method="POST">
                <div class="input-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>