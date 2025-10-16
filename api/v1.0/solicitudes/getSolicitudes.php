<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";

$idUser = "E216"; // usuario actual

// Paso 1: Obtener puesto y autoridad del usuario
$sqlUser = "SELECT puesto, id_autoridad FROM empleados WHERE id = ?";
$stmt = $mysqli_vacaciones->prepare($sqlUser);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "status" => "Error al preparar la consulta SQL"
    ]);
    exit;
}

$stmt->bind_param("s", $idUser); // "s" porque puede ser alfanumérico
$stmt->execute();
$stmt->bind_result($puesto, $autoridad);
if (!$stmt->fetch()) {
    echo json_encode([
        "err" => true,
        "status" => "Usuario no encontrado"
    ]);
    $stmt->close();
    $mysqli_vacaciones->close();
    exit;
}
$stmt->close();

// Paso 2: Obtener grupos autorizados
$sqlAuth = "SELECT id, clave, clave_autorizador 
            FROM autoridad_departamental 
            WHERE clave_autorizador = ? OR id = ?";
$stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
$stmtAuth->bind_param("ss", $autoridad, $autoridad); // "s" para alfanuméricos
$stmtAuth->execute();
$stmtAuth->bind_result($authId, $clave, $claveAutorizador);

$listaGruposAutorizados = [];
while ($stmtAuth->fetch()) {
    $listaGruposAutorizados[] = [
        "id" => $authId,
        "clave" => $clave,
        "clave_autorizador" => $claveAutorizador
    ];
}
$stmtAuth->close();

// Paso 3: Obtener usuarios de cada grupo autorizado
$listaUserAutorizados = [];
foreach ($listaGruposAutorizados as $grupo) {
    $grupoClave = $mysqli_vacaciones->real_escape_string($grupo['id']);
    $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, correo, empresa, id_departamento 
                 FROM empleados 
                 WHERE id_autoridad = '$grupoClave'";
    $result = $mysqli_vacaciones->query($sqlUsers);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listaUserAutorizados[] = [
                "id" => htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8'),
                "nombre" => htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8'),
                "apellido_paterno" => htmlspecialchars($row['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8'),
                "apellido_materno" => htmlspecialchars($row['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8'),
                "puesto" => htmlspecialchars($row['puesto'] ?? '', ENT_QUOTES, 'UTF-8'),
                "correo" => htmlspecialchars($row['correo'] ?? '', ENT_QUOTES, 'UTF-8'),
                "empresa" => htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES, 'UTF-8'),
                "id_departamento" => htmlspecialchars($row['id_departamento'] ?? '', ENT_QUOTES, 'UTF-8')
            ];
        }
    }
}

// Paso previo 1: cargar departamentos
$departamentos = [];
$resultDeptos = $mysqli_vacaciones->query("SELECT id_departamento, nombre FROM departamentos");
if ($resultDeptos) {
    while ($row = $resultDeptos->fetch_assoc()) {
        $departamentos[$row['id_departamento']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo 2: cargar puestos
$puestos = [];
$resultPuestos = $mysqli_intranet->query("SELECT id_archivo, nombre FROM puestos");
if ($resultPuestos) {
    while ($row = $resultPuestos->fetch_assoc()) {
        $puestos[$row['id_archivo']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo 3: cargar empleados con su puesto (para autorizador1)
$empleados = [];
$resultEmps = $mysqli_vacaciones->query("SELECT id, puesto FROM empleados");
if ($resultEmps) {
    while ($row = $resultEmps->fetch_assoc()) {
        $empleados[$row['id']] = $row['puesto'] ?? null;
    }
}


// Paso 4: Construir solicitudes
$listaSolicitudes = [];
foreach ($listaUserAutorizados as $user) {
    $userId = $mysqli_solicitud->real_escape_string($user['id']);
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId'";
    $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

    if ($resultSolicitudes) {
        while ($row = $resultSolicitudes->fetch_assoc()) {
            $solicitudBlindada = array_map(function($v){
                return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
            }, $row);

            // Puesto del solicitante
            $nombrePuesto = '';
            $puestoId = $solicitudBlindada['solicitud_puesto_id'] ?? '';
            if ($puestoId && isset($puestos[$puestoId])) {
                $nombrePuesto = $puestos[$puestoId];
            }

            // Departamento del usuario autorizado
            $nombreDepartamento = '';
            $deptoId = $user['id_departamento'] ?? '';
            if ($deptoId && isset($departamentos[$deptoId])) {
                $nombreDepartamento = $departamentos[$deptoId];
            }

            // ✅ Puesto del autorizador1
            $aut1PuestoNombre = '';
            $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
            if ($aut1Id && isset($empleados[$aut1Id])) {
                $aut1PuestoId = $empleados[$aut1Id];
                if ($aut1PuestoId && isset($puestos[$aut1PuestoId])) {
                    $aut1PuestoNombre = $puestos[$aut1PuestoId];
                }
            }

            $solicitudConUsuario = array_merge($solicitudBlindada, [
                "usuario_id" => $user['id'],
                "usuario_nombre" => $user['nombre'],
                "usuario_apellido_paterno" => $user['apellido_paterno'],
                "usuario_apellido_materno" => $user['apellido_materno'],
                "usuario_puesto" => $user['puesto'],
                "usuario_correo" => $user['correo'] ?? '',
                "usuario_empresa" => $user['empresa'],
                "usuario_id_departamento" => $user['id_departamento'],
                "usuario_departamento_nombre" => $nombreDepartamento,
                "solicitud_nombre_puesto" => $nombrePuesto,
                "solicitud_autorizador1_puesto" => $aut1PuestoNombre // ✅ NUEVO
            ]);

            $listaSolicitudes[] = $solicitudConUsuario;
        }
    }
}

// Paso 5: Devolver JSON
header('Content-Type: application/json');
if (empty($listaSolicitudes)) {
    echo json_encode([
        "err" => false,
        "statusText" => "No se encontraron solicitudes.",
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "err" => false,
        "statusText" => "Solicitudes obtenidas correctamente.",
        "data" => $listaSolicitudes
    ], JSON_UNESCAPED_UNICODE);
}
?>
