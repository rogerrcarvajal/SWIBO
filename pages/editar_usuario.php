<?php
// Incluir el protector de sesión y la conexión a la base de datos
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../db.php';

// --- CONTROL DE ACCESO: SOLO ADMINS ---
if ($_SESSION['usuario_rol'] !== 'Admin') {
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";
$usuario_id = $_GET['id'] ?? null;

// Si no se proporciona un ID, o no es un número, redirigir.
if (!$usuario_id || !is_numeric($usuario_id)) {
    header('Location: gestion_usuarios.php');
    exit();
}

// --- LÓGICA PARA ACTUALIZAR EL USUARIO (CUANDO SE ENVÍA EL FORMULARIO) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $nueva_password = $_POST['nueva_password'] ?? '';

    try {
        // Lógica para evitar que un admin se quite el rol de admin a sí mismo
        if ($usuario_id == $_SESSION['usuario_id'] && $rol !== 'Admin') {
            throw new Exception("No puede cambiar su propio rol a 'Consulta' para evitar bloquearse el acceso.");
        }

        // Si se ingresó una nueva contraseña, la actualizamos.
        if (!empty($nueva_password)) {
            $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, username = :username, rol = :rol, password = :password WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre_completo,
                ':email' => $email,
                ':username' => $username,
                ':rol' => $rol,
                ':password' => $hashed_password,
                ':id' => $usuario_id
            ]);
        } else {
            // Si el campo de contraseña está vacío, no la actualizamos.
            $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, username = :username, rol = :rol WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre_completo,
                ':email' => $email,
                ':username' => $username,
                ':rol' => $rol,
                ':id' => $usuario_id
            ]);
        }
        $mensaje = "✅ Usuario actualizado correctamente.";
    } catch (Exception $e) {
        $mensaje = "⚠️ " . $e->getMessage();
    }
}

// --- OBTENER LOS DATOS ACTUALES DEL USUARIO PARA MOSTRARLOS EN EL FORMULARIO ---
$stmt = $conn->prepare("SELECT id, nombre, email, username, rol FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el usuario con ese ID no existe, redirigir.
if (!$usuario_a_editar) {
    header('Location: gestion_usuarios.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIBO - Editar Usuario</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .form-container {
            background-color: #00224480;
            border:2px solid rgba(255,255,255,0.18);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 20px auto;
        }
        h2 { color: rgba(255, 255, 255, 0.712); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #fff; font-weight: bold; }
        .alert-message.success { background-color: #28a745; }
        .alert-message.error { background-color: #dc3545; }
        .form-actions { margin-top: 20px; }
        .btn-secondary { background-color: #d10000; color: rgba(255, 255, 255, 0.712); padding: 11px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Editar Usuario</h1>
        </header>

        <div class="form-container">
            <h2>Editando a "<?php echo htmlspecialchars($usuario_a_editar['nombre']); ?>"</h2>

            <?php if ($mensaje): ?>
                <p class="alert-message <?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <form action="editar_usuario.php?id=<?php echo $usuario_a_editar['id']; ?>" method="POST">
                <div class="input-group">
                    <label for="nombre_completo">Nombre Completo</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario_a_editar['nombre']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_a_editar['email']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario_a_editar['username']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="rol">Rol del Usuario</label>
                    <select id="rol" name="rol" required>
                        <option value="Admin" <?php echo ($usuario_a_editar['rol'] === 'Admin') ? 'selected' : ''; ?>>Admin (Administrador)</option>
                        <option value="Consulta" <?php echo ($usuario_a_editar['rol'] === 'Consulta') ? 'selected' : ''; ?>>Consulta</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="nueva_password">Nueva Contraseña</label>
                    <input type="password" id="nueva_password" name="nueva_password" placeholder="Dejar en blanco para no cambiar">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-login">Actualizar Usuario</button>
                    <br><br><br>
                    <a href="gestion_usuarios.php" class="btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>