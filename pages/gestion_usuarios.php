<?php
// Incluir el protector de sesión para asegurar que el usuario ha iniciado sesión.
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';

// --- BLOQUE DE CONTROL DE ACCESO ---
// Solo los administradores pueden acceder a esta página.
if ($_SESSION['usuario_rol'] !== 'Admin') {
    // Si no es admin, redirigir al dashboard. Podríamos añadir un mensaje de error.
    header('Location: dashboard.php');
    exit();
}

$mensaje = "";

// --- LÓGICA PARA REGISTRAR UN NUEVO USUARIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
    $nombre_completo = $_POST["nombre_completo"] ?? '';
    $email = $_POST["email"] ?? '';
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $rol = $_POST["rol"] ?? '';

    // Validaciones básicas
    if (empty($nombre_completo) || empty($email) || empty($username) || empty($password) || empty($rol)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } else {
        // Verificar que el username o email no existan
        $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = :username OR email = :email");
        $check_stmt->execute([':username' => $username, ':email' => $email]);

        if ($check_stmt->rowCount() > 0) {
            $mensaje = "⚠️ El nombre de usuario o el email ya están registrados.";
        } else {
            // Encriptar la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario en la base de datos
            $sql = "INSERT INTO usuarios (nombre, email, username, password, rol) VALUES (:nombre, :email, :username, :password, :rol)";
            $insert_stmt = $conn->prepare($sql);
            $insert_stmt->execute([
                ':nombre' => $nombre_completo,
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashed_password,
                ':rol' => $rol
            ]);
            $mensaje = "✅ Usuario registrado correctamente.";
        }
    }
}

// --- OBTENER LA LISTA DE USUARIOS EXISTENTES ---
$usuarios = $conn->query("SELECT id, nombre, username, email, rol FROM usuarios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWIBO - Gestión de Usuarios</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .main-content { padding: 20px 40px; }
        .gestion-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 20px;
        }
        .form-section, .list-section {
            background-color: #00224480;
            border:2px solid rgba(255,255,255,0.18);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-section { flex: 1; min-width: 350px; }
        .list-section { flex: 1.5; min-width: 450px; color: rgba(255, 255, 255, 0.712); }

        h2 { color: rgba(255, 255, 255, 0.712); border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        
        .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .user-table th, .user-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .user-table th { background-color: rgba(158, 0, 0, 0.5); }
        .user-table tr:hover { background-color: rgba(255, 0, 0, 0.274); }
        .actions a {
            margin-right: 10px;
            width: 100%;
            padding: 8px;
            border: none;
            background-color: #d10000;
            color: rgba(255, 255, 255, 0.712);
            font-size: 18px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .actions a.delete { color: #dc3545; }
        
        .alert-message { padding: 10px; border-radius: 5px; margin-bottom: 15px; color: #fff; }
        .alert-message.success { background-color: #28a745; }
        .alert-message.error { background-color: #dc3545; }

    </style>
</head>
<body>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../src/templates/navbar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Gestión de Usuarios del Sistema</h1>
            <p>Crear nuevas cuentas de usuario y administrar las existentes.</p>
        </header>

        <div class="gestion-container">
            <div class="form-section">
                <h2>Registrar Nuevo Usuario</h2>
                <?php if ($mensaje): ?>
                    <p class="alert-message <?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>">
                        <?php echo $mensaje; ?>
                    </p>
                <?php endif; ?>

                <form action="gestion_usuarios.php" method="POST">
                    <div class="input-group">
                        <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Nombre Completo" required>
                    </div>
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <input type="text" id="username" name="username" placeholder="Usuario" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Contraseña" id="password" required>
                        <input type="checkbox" id="show-password" onclick="password.type = this.checked ? 'text' : 'password'">
                    </div>
                    <div class="input-group">
                        <label for="rol">Rol del Usuario</label>
                        <select id="rol" name="rol" required>
                            <option value="">-- Seleccione un rol --</option>
                            <option value="Admin">Admin (Administrador)</option>
                            <option value="Consulta">Consulta</option>
                        </select>
                    </div>
                    <button type="submit" name="registrar_usuario" class="btn-login">Registrar Usuario</button>
                </form>
            </div>

            <div class="list-section">
                <h2>Usuarios Registrados</h2>
                <table class="user-table" color: rgba(255, 255, 255, 0.712);>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                            <td class="actions">
                                <a href="#">Gestionar</a>
                                <?php if ($_SESSION['usuario_id'] != $usuario['id']): // No permitir que un admin se elimine a sí mismo ?>
                                <a href="#" class="delete" onclick="return confirm('¿Está seguro de que desea eliminar a este usuario?');">Eliminar</a>
                                <?php endif; ?>
                            </td>
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