<?php
require "../conexion.php";

header('Content-Type: application/json');

// Validar que 'depto' esté presente y sea un número
if (!isset($_POST['depto']) || !is_numeric($_POST['depto'])) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "ID de departamento inválido o no proporcionado"
    ]);
    exit;
}

// Convertir a entero para mayor seguridad
$depto = (int)$_POST['depto'];

// Preparar la consulta de forma segura
$stmt = $mysqli_vacaciones->prepare("SELECT * FROM puestos WHERE departamento_id = ?");
if (!$stmt) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_vacaciones->error
    ]);
    exit;
}

$stmt->bind_param("i", $depto);

if (!$stmt->execute()) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "Error al ejecutar la consulta: " . $stmt->error
    ]);
    exit;
}

$resultPuestos = $stmt->get_result();

$array = [];
while ($rowPuestos = $resultPuestos->fetch_assoc()) {
    $array[] = $rowPuestos;
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $array,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);
?>


