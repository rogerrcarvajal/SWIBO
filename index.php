<?php
session_start();

// Si ya hay una sesión, redirigir al dashboard.
if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}

require_once 'db.php';
$mensaje_error = "";

// Procesar el formulario cuando se envía.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password_ingresada = $_POST['password'] ?? '';

    if (empty($username) || empty($password_ingresada)) {
        $mensaje_error = "⚠️ Por favor, ingrese usuario y contraseña.";
    } else {
        try {
            // Preparar y ejecutar la consulta.
            $sql = "SELECT id, nombre, password, rol FROM usuarios WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':username' => $username]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si se encontró el usuario y la contraseña coincide.
            if ($usuario && password_verify($password_ingresada, $usuario['password'])) {
                // Éxito: Guardar en sesión y redirigir.
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                header("Location: pages/dashboard.php");
                exit();
            } else {
                // Fracaso: Establecer mensaje de error.
                $mensaje_error = "⚠️ Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error de conexión. Intente más tarde.";
            // Para depurar, podrías loguear el error: error_log($e->getMessage());
        }
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
                    <input type="text" id="username" name="username" placeholder="Usuario" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contraseña" id="password" required>
                    <input type="checkbox" id="show-password" onclick="password.type = this.checked ? 'text' : 'password'">
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>