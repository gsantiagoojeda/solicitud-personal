<?php
require "../conexion_solicitud.php";
require_once __DIR__ . '/../mail/enviar_correo.php';

header('Content-Type: application/json');

// --- 1. Obtener y Sanitizar Datos ---

$id = $_POST['id'] ?? null;

// Parámetros de autorización
$autorizador = $_POST['autorizador'] ?? null;
$level = $_POST['level'] ?? null;

$dateAuth = date("Y-m-d H:i:s");

// --- 2. Lógica de Asignación de Autorización ---
$sql;
if ($level == 3) {
$sql = "
UPDATE sp_solicitud SET
    solicitud_autorizador3 = ?,
    solicitud_date_autorizacion3 = ?
WHERE solicitud_id = ?
";
} 


$stmt = $mysqli_solicitud->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_solicitud->error,
        "postData" => $_POST
    ]);
    exit;
}


$stmt->bind_param(
    "sss",
    $autorizador,
    $dateAuth,
    $id 
);

// --- 5. Ejecutar y Cerrar ---
if ($stmt->execute()) {
    echo json_encode([
        "err" => false,
        "statusText" => "Vacante RECLUTADA"
    ]);
   
   
}

$stmt->close();
$mysqli_solicitud->close();
?>