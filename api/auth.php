<?php
// api/auth.php

// Iniciar la sesión para poder almacenar datos del usuario
session_start();

// Incluir el archivo de conexión a la base de datos
require_once '../src/db.php';

// Verificar que los datos lleguen por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password_ingresada = $_POST['password'] ?? '';

    if (empty($email) || empty($password_ingresada)) {
        header('Location: ../index.php?error=empty');
        exit();
    }

    try {
        // Preparar la consulta para buscar al usuario por username
        $stmt = $pdo->prepare("SELECT id, nombre, username, password, rol FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();

        // Verificar si se encontró un usuario y si la contraseña es correcta
        // Usamos password_verify() para comparar la contraseña ingresada con el hash guardado
        if ($usuario && password_verify($password_ingresada, $usuario['password'])) {
            
            // La autenticación es exitosa, guardamos los datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirigir al dashboard
            header('Location: ../pages/dashboard.php');
            exit();

        } else {
            // Credenciales incorrectas, redirigir al login con un mensaje de error
            header('Location: ../index.php?error=1');
            exit();
        }

    } catch (PDOException $e) {
        // En caso de un error de base de datos
        // Idealmente, registrar este error en un log
        header('Location: ../index.php?error=db');
        exit();
    }
} else {
    // Si no es una petición POST, redirigir al inicio
    header('Location: ../index.php');
    exit();
}
?>