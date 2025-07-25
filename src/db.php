<?php
// src/db.php

// --- Configuración de la Base de Datos ---
$host = 'localhost';      // O la IP del servidor de BD
$port = '5432';           // Puerto por defecto de PostgreSQL
$dbname = 'swibo_db';     // El nombre que le diste a tu base de datos
$user = 'postgres';       // Tu usuario de PostgreSQL
$password = '4674'; // Tu contraseña de PostgreSQL

// --- Cadena de Conexión (DSN) ---
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    // --- Crear la instancia de PDO ---
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Habilitar excepciones para errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devolver resultados como arrays asociativos
    ]);
} catch (PDOException $e) {
    // --- Manejo de errores de conexión ---
    // En un entorno de producción, no muestres detalles del error al usuario.
    // Registra el error en un archivo de log.
    die("Error: No se pudo conectar a la base de datos. " . $e->getMessage());
}
?>