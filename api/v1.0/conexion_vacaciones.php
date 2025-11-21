<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "app-vacaciones";
$password = "Zeal_2024$$";
$dbname = "app_vacaciones";  // Nombre de la base de datos

$mysqli_vacaciones = new mysqli($servername, $username, $password, $dbname);

if (!$mysqli_vacaciones->set_charset("utf8mb4")) {
    die("Error al configurar el charset UTF-8: " . $mysqli_vacaciones->error);
}

// Cerrar la conexión al finalizar

?>
