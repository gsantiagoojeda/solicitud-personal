<?php
require "../conexion.php";

header('Content-Type: application/json');

// Validar que 'id' esté presente y sea un número
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        "depto" => null,
        "err" => true,
        "statusText" => "ID inválido o no proporcionado"
    ]);
    exit;
}

// Convertir a entero para mayor seguridad
$id = (int)$_POST['id'];

// Preparar la consulta
$stmt = $mysqli_vacaciones->prepare("SELECT * FROM departamentos WHERE id_departamento = ?");
if (!$stmt) {
    echo json_encode([
        "depto" => null,
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_vacaciones->error
    ]);
    exit;
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    echo json_encode([
        "depto" => null,
        "err" => true,
        "statusText" => "Error al ejecutar la consulta: " . $stmt->error
    ]);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "depto" => null,
        "err" => false,
        "statusText" => "Departamento no encontrado"
    ]);
    exit;
}

$depto = $result->fetch_assoc();

echo json_encode([
    "depto" => $depto,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);
?>
