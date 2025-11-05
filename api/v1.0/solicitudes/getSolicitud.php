<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
$sueldoId = $solicitud['solicitud_sueldo_id'] ?? null;
$horarioId = $solicitud['solicitud_horario_id'] ?? null;
$solicitanteId = $solicitud['solicitud_solicitante_id'] ?? null;
$autorizador1Id = $solicitud['solicitud_autorizador1_id'] ?? null;

if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id_archivo = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $stmtPuesto->store_result();

        if ($stmtPuesto->num_rows > 0) {
            $stmtPuesto->bind_result($puestoNombre);
            $stmtPuesto->fetch();
            $solicitud['solicitud_puesto_nombre'] = $puestoNombre;
            $stmtPuesto->close();
        } else {
            $solicitud['solicitud_puesto_nombre'] = null;
        }
    } else {
        $solicitud['solicitud_puesto_nombre'] = null;
    }
} else {
    $solicitud['solicitud_puesto_nombre'] = null;
}

if ($sueldoId) {
    $stmtSueldo = $mysqli_solicitud->prepare("SELECT sueldo_nombre, sueldo_cantidad FROM sp_sueldos WHERE sueldo_id = ?");
    if ($stmtSueldo) {
        $stmtSueldo->bind_param("s", $sueldoId);
        $stmtSueldo->execute();
        $stmtSueldo->store_result();

        if ($stmtSueldo->num_rows > 0) {
            $stmtSueldo->bind_result($sueldoNombre, $sueldoCantidad);
            $stmtSueldo->fetch();
            $solicitud['solicitud_sueldo'] = $sueldoNombre . ":" .$sueldoCantidad;
            $stmtSueldo->close();
        } else {
            $solicitud['solicitud_sueldo'] = null;
        }
    } else {
        $solicitud['solicitud_sueldo'] = null;
    }
} else {
    $solicitud['solicitud_sueldo'] = null;
}


if ($horarioId) {
    $stmtHorario = $mysqli_turnos->prepare("SELECT nombre_turno, hora_inicio, hora_termino FROM turnos WHERE id_turnos = ?");
    if ($stmtHorario) {
        $stmtHorario->bind_param("s", $horarioId);
        $stmtHorario->execute();
        $stmtHorario->store_result();

        if ($stmtHorario->num_rows > 0) {
            $stmtHorario->bind_result($horarioNombre, $horaInicio, $horaFinal);
            $stmtHorario->fetch();
            $solicitud['solicitud_horario'] = $horarioNombre . " :" .$horaInicio. " a ". $horaFinal;
            $stmtHorario->close();
        } else {
            $solicitud['solicitud_horario'] = null;
        }
    } else {
        $solicitud['solicitud_horario'] = null;
    }
} else {
    $solicitud['solicitud_horario'] = null;
}

if ($solicitanteId) {
    $stmtSolicitante = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtSolicitante) {
        $stmtSolicitante->bind_param("s", $solicitanteId);
        $stmtSolicitante->execute();
        $stmtSolicitante->store_result();

        if ($stmtSolicitante->num_rows > 0) {
            $stmtSolicitante->bind_result($solicitanteNombre, $solicitanteAP, $solicitanteAM);
            $stmtSolicitante->fetch();
            $solicitud['solicitud_solicitante'] = $solicitanteNombre . " ". $solicitanteAP . " " . $solicitanteAM;
            $stmtSolicitante->close();
        } else {
            $solicitud['solicitud_solicitante'] = null;
        }
    } else {
        $solicitud['solicitud_solicitante'] = null;
    }
} else {
    $solicitud['solicitud_solicitante'] = null;
}

if ($autorizador1Id) {
    $stmtAuth1 = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtAuth1) {
        $stmtAuth1->bind_param("s", $autorizador1Id);
        $stmtAuth1->execute();
        $stmtAuth1->store_result();

        if ($stmtAuth1->num_rows > 0) {
            $stmtAuth1->bind_result($solicitanteNombre, $solicitanteAP, $solicitanteAM);
            $stmtAuth1->fetch();
            $solicitud['solicitud_autorizador1'] = $solicitanteNombre . $solicitanteAP . $solicitanteAM;
            $stmtAuth1->close();
        } else {
            $solicitud['solicitud_autorizador1'] = null;
        }
    } else {
        $solicitud['solicitud_autorizador1'] = null;
    }
} else {
    $solicitud['solicitud_autorizador1'] = null;
}


// 🧩 Normaliza valores vacíos o nulos antes de codificar
function normalize_values($array) {
    foreach ($array as $key => $value) {
      echo ("entro" . $value);
        if (is_array($value)) {
            $array[$key] = normalize_values($value);
        } elseif (is_null($value) || $value === false) {
            $array[$key] = ""; // o usa null si prefieres
        } elseif (is_string($value)) {
            // Asegura codificación válida UTF-8
            $array[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
        }
    }
    return $array;
}

$solicitud = normalize_values($solicitud);


print_r($solicitud);

echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
