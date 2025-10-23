<?php
require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";
require "../conexion_turnos.php";

header('Content-Type: application/json');

// Validar que 'id' esté presente y sea un número
// if (!isset($_POST['id_solicitud']) || !is_numeric($_POST['id_solicitud'])) {
//     echo json_encode([
//         "solicitud" => null,
//         "err" => true,
//         "statusText" => "ID inválido o no proporcionado"
//     ]);
//     exit;
// }

// $id = $_POST['id_solicitud'];
$id = "1";

// Consulta de la solicitud
$stmt = $mysqli_vacaciones->prepare("SELECT * FROM solicitudes WHERE id_solicitud = ?");
if (!$stmt) {
    echo json_encode([
        "solicitud" => null,
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_vacaciones->error
    ]);
    exit;
}

$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "solicitud" => null,
        "err" => false,
        "statusText" => "Solicitud no encontrada"
    ]);
    exit;
}

$solicitud = $result->fetch_assoc();

// Obtener nombre del puesto desde $mysqli_intranet
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;

if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $resPuesto = $stmtPuesto->get_result();
        
        if ($resPuesto->num_rows > 0) {
            $puestoData = $resPuesto->fetch_assoc();
            $solicitud['solicitud_puesto_nombre'] = $puestoData['nombre'];
        } else {
            $solicitud['solicitud_puesto_nombre'] = null;
        }
    } else {
        $solicitud['solicitud_puesto_nombre'] = null;
    }
} else {
    $solicitud['solicitud_puesto_nombre'] = null;
}

echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);
