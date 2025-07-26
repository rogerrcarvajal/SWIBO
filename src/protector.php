<?php
// src/protector.php

// Inicia la sesión si no está iniciada ya.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si la variable de sesión del ID de usuario NO está establecida.
if (!isset($_SESSION['usuario'])) {
    // Si no ha iniciado sesión, destruye cualquier dato de sesión residual.
    session_destroy();
    
    // Redirige al usuario a la página de login.
    // Usamos una ruta absoluta desde la raíz del proyecto para mayor seguridad.
    header("Location: /swibo/index.php"); // Asegúrate que '/swibo/' es el nombre de tu carpeta de proyecto.
    exit(); // Detiene la ejecución del script de la página actual.
}
?>s