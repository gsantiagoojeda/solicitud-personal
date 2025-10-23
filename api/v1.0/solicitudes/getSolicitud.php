<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";
require "../conexion_turnos.php";

header('Content-Type: application/json');

// $id = $_POST['id_solicitud'];
$id = "1";

// Consulta de la solicitud
$stmt = $mysqli_solicitud->prepare("SELECT * FROM sp_solicitud WHERE solicitud_id = ?");
if (!$stmt) {
    echo json_encode([
        "solicitud" => null,
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_solicitud->error
    ]);
    exit;
}

$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        "solicitud" => null,
        "err" => false,
        "statusText" => "Solicitud no encontrada"
    ]);
    exit;
}

// Obtener metadata para asociar columnas
$meta = $stmt->result_metadata();
$fields = [];
$row = [];

while ($field = $meta->fetch_field()) {
    $fields[] = &$row[$field->name]; 
}

call_user_func_array([$stmt, 'bind_result'], $fields);
$stmt->fetch();

// Copiar valores a un array asociativo
$solicitud = [];
foreach ($row as $key => $val) {
    $solicitud[$key] = $val;
}

// Obtener nombre del puesto desde $mysqli_intranet
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;

if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $stmtPuesto->store_result();

        if ($stmtPuesto->num_rows > 0) {
            $stmtPuesto->bind_result($puestoNombre);
            $stmtPuesto->fetch();
            $solicitud['solicitud_puesto_nombre'] = $puestoNombre;
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
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
