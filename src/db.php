<?php
    $host = "localhost";
    $db = "swibo_db";
    $user = "postgres";
    $password = "4674";

    try {
        $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Error en la conexión: " . $e->getMessage());
    }
?>