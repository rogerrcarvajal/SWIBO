<?php
// api/auth.php

session_start();
require_once '../src/db.php'; // Usa tu archivo db.php

// Verificar que los datos lleguen por método POST
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