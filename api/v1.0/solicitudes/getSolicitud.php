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
$id = "1"; // ID de prueba

/**
 * Función auxiliar para obtener un array asociativo del resultado de un prepared statement 
 * usando el método antiguo (sin mysqlnd).
 * @param mysqli_stmt $stmt El statement ejecutado.
 * @return array|null Un array asociativo de la fila, o null si no hay filas.
 */
function get_assoc_result($stmt) {
    $meta = $stmt->result_metadata();
    if (!$meta) {
        return null;
    }
    
    $fields = [];
    $row = [];
    
    // 1. Obtener los nombres de las columnas y preparar las referencias
    while ($field = $meta->fetch_field()) {
        $fields[] = &$row[$field->name]; 
    }
    
    // 2. Vincular las referencias a los resultados
    if (!call_user_func_array([$stmt, 'bind_result'], $fields)) {
        return null;
    }
    
    // 3. Obtener la fila (fetch)
    if ($stmt->fetch()) {
        // 4. Copiar los valores a un nuevo array para romper la referencia
        $result_row = [];
        foreach ($row as $key => $val) {
            // Aplicar trim inmediatamente para limpiar cualquier espacio/caracter invisible que
            // el fetch haya devuelto. Esto es CRÍTICO sin mysqlnd.
            if (is_string($val)) {
                $result_row[$key] = trim($val);
            } else {
                $result_row[$key] = $val;
            }
        }
        return $result_row;
    }
    return null;
}

// --- 1. Consulta de la solicitud principal ---
$stmt = $mysqli_solicitud->prepare("SELECT * FROM sp_solicitud WHERE solicitud_id = ?");
if (!$stmt) {
    // Manejo de error de preparación
    echo json_encode(["solicitud" => null, "err" => true, "statusText" => "Error al preparar la consulta: " . $mysqli_solicitud->error]);
    exit;
}

$stmt->bind_param("s", $id);
$stmt->execute();
$stmt->store_result();

$solicitud = get_assoc_result($stmt);
$stmt->close(); // Cerramos el statement principal

if (!$solicitud) {
    echo json_encode(["solicitud" => null, "err" => false, "statusText" => "Solicitud no encontrada"]);
    exit;
}

// Obtener IDs para consultas relacionadas
$puestoId = $solicitud['solicitud_puesto_id'] ?? null;
$sueldoId = $solicitud['solicitud_sueldo_id'] ?? null;
$horarioId = $solicitud['solicitud_horario_id'] ?? null;
$solicitanteId = $solicitud['solicitud_solicitante_id'] ?? null;
$autorizador1Id = $solicitud['solicitud_autorizador1_id'] ?? null;

// Las consultas secundarias usan el mismo patrón simple de bind/fetch
// -------------------------------------------------------------------

// --- 2. Obtener nombre del Puesto ---
$solicitud['solicitud_puesto_nombre'] = null;
if ($puestoId) {
    $stmtPuesto = $mysqli_intranet->prepare("SELECT nombre FROM puestos WHERE id_archivo = ?");
    if ($stmtPuesto) {
        $stmtPuesto->bind_param("s", $puestoId);
        $stmtPuesto->execute();
        $stmtPuesto->store_result();
        if ($stmtPuesto->num_rows > 0) {
            $puestoNombre = '';
            $stmtPuesto->bind_result($puestoNombre);
            $stmtPuesto->fetch();
            $solicitud['solicitud_puesto_nombre'] = trim($puestoNombre);
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
        $stmtSueldo->store_result();
        if ($stmtSueldo->num_rows > 0) {
            $sueldoNombre = '';
            $sueldoCantidad = '';
            $stmtSueldo->bind_result($sueldoNombre, $sueldoCantidad);
            $stmtSueldo->fetch();
            // Aplicamos trim y concatenamos
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
        $stmtHorario->store_result();
        if ($stmtHorario->num_rows > 0) {
            $horarioNombre = '';
            $horaInicio = '';
            $horaFinal = '';
            $stmtHorario->bind_result($horarioNombre, $horaInicio, $horaFinal);
            $stmtHorario->fetch();
            // Aplicamos trim y concatenamos
            $solicitud['solicitud_horario'] = trim($horarioNombre) . " :" . trim($horaInicio) . " a " . trim($horaFinal);
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
        $stmtSolicitante->store_result();
        if ($stmtSolicitante->num_rows > 0) {
            $solicitanteNombre = '';
            $solicitanteAP = '';
            $solicitanteAM = '';
            $stmtSolicitante->bind_result($solicitanteNombre, $solicitanteAP, $solicitanteAM);
            $stmtSolicitante->fetch();
            // Aplicamos trim a cada parte del nombre
            $solicitud['solicitud_solicitante'] = trim($solicitanteNombre) . " ". trim($solicitanteAP) . " " . trim($solicitanteAM);
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
        $stmtAuth1->store_result();
        if ($stmtAuth1->num_rows > 0) {
            $auth1Nombre = '';
            $auth1AP = '';
            $auth1AM = '';
            $stmtAuth1->bind_result($auth1Nombre, $auth1AP, $auth1AM);
            $stmtAuth1->fetch();
            // Aplicamos trim a cada parte del nombre
            $solicitud['solicitud_autorizador1'] = trim($auth1Nombre) . " " . trim($auth1AP) . " " . trim($auth1AM);
        }
        $stmtAuth1->close();
    }
}

// -------------------------------------------------------------------
// Normalización Final
// La limpieza crítica (trim) ya se hizo en el momento del fetch.
// Esta función ahora solo se centra en asegurar "" y UTF-8.

function normalize_values_simple($array) {
    foreach ($array as $key => $value) {
        // La depuración se omite para la versión final, pero la lógica es:
        // Si el valor es NULL, false, o una cadena vacía (después de trim), lo hacemos ""
        if (is_null($value) || $value === false || $value === '') { 
            $array[$key] = "";
        } elseif (is_string($value)) {
            // Aseguramos codificación válida UTF-8
            $array[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
        } 
    }
    return $array;
}

// --- Salida (Depuración) ---
echo "Primer printr del array antes de normalizar:\n";
print_r($solicitud);

$solicitud = normalize_values_simple($solicitud);

echo "\nSegundo printr del array DESPUÉS de normalizar:\n";
print_r($solicitud);

// --- Salida Final JSON ---
echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>