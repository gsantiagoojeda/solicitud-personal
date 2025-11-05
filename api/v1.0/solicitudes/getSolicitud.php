<?php
require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";
require "../conexion_turnos.php";

header('Content-Type: application/json');

$id = $_POST['id_solicitud'] ?? "1";

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

// --- Enlazar campos adicionales ---
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;
$sueldoId = $solicitud['solicitud_sueldo_id'] ?? null;
$horarioId = $solicitud['solicitud_horario_id'] ?? null;
$solicitanteId = $solicitud['solicitud_solicitante_id'] ?? null;
$autorizador1Id = $solicitud['solicitud_autorizador1_id'] ?? null;

// --- Normalizar nulos y codificación ---
function sanitizeArray($data) {
    foreach ($data as $key => $value) {
        if (is_null($value) || $value === false) {
            $data[$key] = "";
        } elseif (is_string($value)) {
            $data[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
        }
    }
    return $data;
}

print_r($solicitud);
$solicitud = sanitizeArray($solicitud);

// --- Respuesta final ---
echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error JSON: " . json_last_error_msg());
}
