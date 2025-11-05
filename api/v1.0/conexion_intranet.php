<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "user_intranet_alze";
$password = "intranetalze$0$5";
$dbname = "intranet_alze";  // Nombre de la base de datos

$mysqli_intranet = new mysqli($servername, $username, $password, $dbname);

if (!$mysqli_intranet->set_charset("utf8mb4")) {
    die("Error al configurar el charset UTF-8: " . $mysqli_intranet->error);
}

?>
