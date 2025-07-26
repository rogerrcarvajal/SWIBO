<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: /swibo/pages/dashboard.php");
    exit();
}
// Incluir configuración y conexión a la base de datos
require_once __DIR__ . '/src/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM usuarios WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificación segura y funcional
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Guardamos todo el array de usuario en la sesión
        $_SESSION['usuario'] = $usuario;
        
        header("Location: /swibo/pages/dashboard.php");
        exit();
    } else {
        $mensaje = "⚠️ Usuario o contraseña incorrectos.";
    }
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
            <h1>Sistema Web para el<br>Control de Inventario</h1>
            
            <?php if (!empty($mensaje_error)): ?>
                <p class="error-message"><?php echo $mensaje_error; ?></p>
            <?php endif; ?>

            <form action="index.php" method="POST">
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