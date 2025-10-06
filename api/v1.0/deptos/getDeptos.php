<?php
require "../conexion.php";

header('Content-Type: application/json');

$sqlDeptos = "SELECT * FROM departamentos";
$resultDeptos = $mysqli_vacaciones->query($sqlDeptos);

// Verificar si ocurrió un error en la consulta
if (!$resultDeptos) {
    echo json_encode([
        "deptos" => [],
        "err" => true,
        "statusText" => "Error al ejecutar la consulta: " . $mysqli_vacaciones->error
    ]);
    exit;
}

$array = [];
while ($rowDeptos = $resultDeptos->fetch_assoc()) {
    $array[] = $rowDeptos;
}

// Respuesta exitosa
echo json_encode([
    "deptos" => $array,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);
?>

