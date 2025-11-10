<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); // Mantén esto descomentado para ver errores

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";
require "../conexion_turnos.php";

header('Content-Type: application/json');

 $id = $_POST['id_solicitud'];
// $id = "1"; // ID de prueba

// --- 1. Consulta de la solicitud principal ---
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
// >> APROVECHAMOS mysqlnd: Usamos get_result() y fetch_assoc() <<
$result = $stmt->get_result(); 

if ($result->num_rows === 0) {
    echo json_encode([
        "solicitud" => null,
        "err" => false,
        "statusText" => "Solicitud no encontrada"
    ]);
    $stmt->close();
    exit;
}

// Obtener el array asociativo. Los NULL de la DB se convierten a null de PHP.
$solicitud = $result->fetch_assoc();
$stmt->close(); 


// Obtener IDs para consultas relacionadas
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;
$sueldoId = $solicitud['solicitud_sueldo_id'] ?? null;
$horarioId = $solicitud['solicitud_horario_id'] ?? null;
$solicitanteId = $solicitud['solicitud_solicitante_id'] ?? null;
$autorizador1Id = $solicitud['solicitud_autorizador1_id'] ?? null;


// --- 2. Obtener nombre del Puesto ---
$solicitud['solicitud_puesto_nombre'] = null;
if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id_archivo = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $resultPuesto = $stmtPuesto->get_result(); // Usando get_result()
        if ($resultPuesto->num_rows > 0) {
            $rowPuesto = $resultPuesto->fetch_assoc();
            $solicitud['solicitud_puesto_nombre'] = $rowPuesto['nombre'];
        }
        $stmtPuesto->close();
    }
}

// --- 3. Obtener Sueldo ---
$solicitud['solicitud_sueldo'] = null;
if ($sueldoId) {
    $stmtSueldo = $mysqli_solicitud->prepare("SELECT sueldo_nombre, sueldo_cantidad FROM sp_sueldos WHERE sueldo_id = ?");
    if ($stmtSueldo) {
        $stmtSueldo->bind_param("s", $sueldoId);
        $stmtSueldo->execute();
        $resultSueldo = $stmtSueldo->get_result();
        if ($resultSueldo->num_rows > 0) {
            $rowSueldo = $resultSueldo->fetch_assoc();
            $sueldoNombre = $rowSueldo['sueldo_nombre'] ?? '';
            $sueldoCantidad = $rowSueldo['sueldo_cantidad'] ?? '';
            $solicitud['solicitud_sueldo'] = trim($sueldoNombre) . ":" . trim($sueldoCantidad);
        }
        $stmtSueldo->close();
    }
}

// --- 4. Obtener Horario ---
$solicitud['solicitud_horario'] = null;
if ($horarioId) {
    $stmtHorario = $mysqli_turnos->prepare("SELECT nombre_turno, hora_inicio, hora_termino FROM turnos WHERE id_turnos = ?");
    if ($stmtHorario) {
        $stmtHorario->bind_param("s", $horarioId);
        $stmtHorario->execute();
        $resultHorario = $stmtHorario->get_result();
        if ($resultHorario->num_rows > 0) {
            $rowHorario = $resultHorario->fetch_assoc();
            $nombre = $rowHorario['nombre_turno'] ?? '';
            $inicio = $rowHorario['hora_inicio'] ?? '';
            $final = $rowHorario['hora_termino'] ?? '';
            $solicitud['solicitud_horario'] = trim($nombre) . " :" . trim($inicio) . " a " . trim($final);
        }
        $stmtHorario->close();
    }
}

// --- 5. Obtener Solicitante ---
$solicitud['solicitud_solicitante'] = null;
if ($solicitanteId) {
    $stmtSolicitante = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtSolicitante) {
        $stmtSolicitante->bind_param("s", $solicitanteId);
        $stmtSolicitante->execute();
        $resultSolicitante = $stmtSolicitante->get_result();
        if ($resultSolicitante->num_rows > 0) {
            $row = $resultSolicitante->fetch_assoc();
            $nombre = $row['nombre'] ?? '';
            $ap = $row['apellido_paterno'] ?? '';
            $am = $row['apellido_materno'] ?? '';
            $solicitud['solicitud_solicitante'] = trim($nombre) . " ". trim($ap) . " " . trim($am);
        }
        $stmtSolicitante->close();
    }
}

// --- 6. Obtener Autorizador 1 ---
$solicitud['solicitud_autorizador1'] = null;
if ($autorizador1Id) {
    $stmtAuth1 = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtAuth1) {
        $stmtAuth1->bind_param("s", $autorizador1Id);
        $stmtAuth1->execute();
        $resultAuth1 = $stmtAuth1->get_result();
        if ($resultAuth1->num_rows > 0) {
            $row = $resultAuth1->fetch_assoc();
            $nombre = $row['nombre'] ?? '';
            $ap = $row['apellido_paterno'] ?? '';
            $am = $row['apellido_materno'] ?? '';
            $solicitud['solicitud_autorizador1'] = trim($nombre) . " " . trim($ap) . " " . trim($am);
        }
        $stmtAuth1->close();
    }
}

// --- 7. Normalización Mínima y Segura (SÓLO para asegurar el formato JSON) ---
/**
 * Normaliza cualquier valor que sea null, false o una cadena vacía (luego del trim en la concatenación) a "".
 */
function normalize_values_minimal($array) {
    foreach ($array as $key => $value) {
        // **OPCIONAL:** Se puede quitar si sabes que todos los campos vienen limpios
        if (is_null($value) || $value === false || (is_string($value) && trim($value) === '')) { 
            $array[$key] = "";
        }
    }
    return $array;
}



$solicitud = normalize_values_minimal($solicitud);


// --- Salida Final JSON ---
echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>