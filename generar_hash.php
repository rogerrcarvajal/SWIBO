<?php
// Define la contraseña que quieres encriptar.
$passwordPlano = 'admin'; // Puedes cambiar 'admin' por la contraseña que desees.

// Genera el hash seguro.
$hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

// Muestra el hash en pantalla.
echo "Copia y pega este hash en tu base de datos para el usuario 'admin':<br><br>";
echo "<strong>" . $hash . "</strong>";
?>