<?php
// src/db.php
$host = "localhost";
$db = "swibo_db";
$user = "postgres";
$password = "4674"; // Tu contraseña de PostgreSQL

try {
    $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Si la conexión falla, el script se detendrá y mostrará un error.
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>