<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); // Descomentar para depuración

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";
require "../conexion_turnos.php";

header('Content-Type: application/json');

// $id = $_POST['id_solicitud'];
$id = "1"; // Valor de prueba

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

$solicitud = $result->fetch_assoc();
$stmt->close();

if (!$solicitud) {
     $solicitud = [];
}

// Obtener IDs para consultas relacionadas
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;
$sueldoId = $solicitud['solicitud_sueldo_id'] ?? null;
$horarioId = $solicitud['solicitud_horario_id'] ?? null;
$solicitanteId = $solicitud['solicitud_solicitante_id'] ?? null;
$autorizador1Id = $solicitud['solicitud_autorizador1_id'] ?? null;

// --- 2. Obtener nombre del Puesto ---
if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id_archivo = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $resultPuesto = $stmtPuesto->get_result();

        if ($resultPuesto->num_rows > 0) {
            $rowPuesto = $resultPuesto->fetch_assoc();
            $solicitud['solicitud_puesto_nombre'] = $rowPuesto['nombre'];
        } else {
            $solicitud['solicitud_puesto_nombre'] = null;
        }
        $stmtPuesto->close();
    } else {
        $solicitud['solicitud_puesto_nombre'] = null;
    }
} else {
    $solicitud['solicitud_puesto_nombre'] = null;
}

// --- 3. Obtener Sueldo ---
if ($sueldoId) {
    $stmtSueldo = $mysqli_solicitud->prepare("SELECT sueldo_nombre, sueldo_cantidad FROM sp_sueldos WHERE sueldo_id = ?");
    if ($stmtSueldo) {
        $stmtSueldo->bind_param("s", $sueldoId);
        $stmtSueldo->execute();
        $resultSueldo = $stmtSueldo->get_result();

        if ($resultSueldo->num_rows > 0) {
            $rowSueldo = $resultSueldo->fetch_assoc();
            $solicitud['solicitud_sueldo'] = $rowSueldo['sueldo_nombre'] . ":" .$rowSueldo['sueldo_cantidad'];
        } else {
            $solicitud['solicitud_sueldo'] = null;
        }
        $stmtSueldo->close();
    } else {
        $solicitud['solicitud_sueldo'] = null;
    }
} else {
    $solicitud['solicitud_sueldo'] = null;
}

// --- 4. Obtener Horario ---
if ($horarioId) {
    $stmtHorario = $mysqli_turnos->prepare("SELECT nombre_turno, hora_inicio, hora_termino FROM turnos WHERE id_turnos = ?");
    if ($stmtHorario) {
        $stmtHorario->bind_param("s", $horarioId);
        $stmtHorario->execute();
        $resultHorario = $stmtHorario->get_result();

        if ($resultHorario->num_rows > 0) {
            $rowHorario = $resultHorario->fetch_assoc();
            $solicitud['solicitud_horario'] = $rowHorario['nombre_turno'] . " :" .$rowHorario['hora_inicio']. " a ". $rowHorario['hora_termino'];
        } else {
            $solicitud['solicitud_horario'] = null;
        }
        $stmtHorario->close();
    } else {
        $solicitud['solicitud_horario'] = null;
    }
} else {
    $solicitud['solicitud_horario'] = null;
}

// --- 5. Obtener Solicitante ---
if ($solicitanteId) {
    $stmtSolicitante = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtSolicitante) {
        $stmtSolicitante->bind_param("s", $solicitanteId);
        $stmtSolicitante->execute();
        $resultSolicitante = $stmtSolicitante->get_result();

        if ($resultSolicitante->num_rows > 0) {
            $rowSolicitante = $resultSolicitante->fetch_assoc();
            $solicitud['solicitud_solicitante'] = $rowSolicitante['nombre'] . " ". $rowSolicitante['apellido_paterno'] . " " . $rowSolicitante['apellido_materno'];
        } else {
            $solicitud['solicitud_solicitante'] = null;
        }
        $stmtSolicitante->close();
    } else {
        $solicitud['solicitud_solicitante'] = null;
    }
} else {
    $solicitud['solicitud_solicitante'] = null;
}

// --- 6. Obtener Autorizador 1 ---
if ($autorizador1Id) {
    $stmtAuth1 = $mysqli_vacaciones->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM empleados WHERE id= ?");
    if ($stmtAuth1) {
        $stmtAuth1->bind_param("s", $autorizador1Id);
        $stmtAuth1->execute();
        $resultAuth1 = $stmtAuth1->get_result();

        if ($resultAuth1->num_rows > 0) {
            $rowAuth1 = $resultAuth1->fetch_assoc();
            $solicitud['solicitud_autorizador1'] = $rowAuth1['nombre'] . " " . $rowAuth1['apellido_paterno'] . " " . $rowAuth1['apellido_materno'];
        } else {
            $solicitud['solicitud_autorizador1'] = null;
        }
        $stmtAuth1->close();
    } else {
        $solicitud['solicitud_autorizador1'] = null;
    }
} else {
    $solicitud['solicitud_autorizador1'] = null;
}

// --- 7. PARCHE FUERTE: Limpieza y Conversión Inicial ANTES de normalize_values ---
foreach ($solicitud as $key => $value) {
    if (is_string($value)) {
        // Limpiamos espacios en cadenas
        $solicitud[$key] = trim($value); 
    } elseif (empty($value) && !is_numeric($value)) { 
        // Si es empty (que incluye NULL, false, '') y NO es el número 0
        $solicitud[$key] = null; // Lo forzamos a NULL
    } 
    // Los números 0, int, float y cadenas con contenido pasan sin cambios
}
// -----------------------------------------------------------------------------

// --- 8. Función de Normalización ULTRA-ROBUSTA ---
function normalize_values($array) {
    foreach ($array as $key => $value) {
        echo "Procesando campo: {$key} → ";

        if (is_array($value)) {
            echo "[array]\n";
            $array[$key] = normalize_values($value); 
        } elseif (is_null($value) || $value === false || $value === '') {
            // Atrapa NULL, FALSE, y la cadena vacía ("") resultante del trim() en el parche.
            echo "NULL/FALSE/VACIO (convertido a \"\")\n";
            $array[$key] = "";
        } elseif (is_string($value)) {
            // Limpieza más agresiva de caracteres invisibles
            $cleaned_value = preg_replace('/[\p{C}\p{Z}]/u', '', $value);

            if ($cleaned_value === "") {
                echo "Cadena limpia vacía (convertido a \"\")\n";
                $array[$key] = "";
            } else {
                echo "String (limpio): {$cleaned_value}\n";
                $array[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
            }
        } else {
            echo "Otro tipo (" . gettype($value) . "): {$value}\n";
        }
    }
    return $array;
}

// --- 9. Aplicar y Mostrar Resultados (Depuración) ---
print_r($solicitud);
$solicitud = normalize_values($solicitud);

echo "segundo printr \n";
print_r($solicitud);

// --- 10. Salida Final JSON ---
echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>