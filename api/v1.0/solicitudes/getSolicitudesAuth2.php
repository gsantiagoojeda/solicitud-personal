<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";


// $idUser = $_POST['user-id']; // usuario actual
$idUser = "391"; // usuario actual

$filterYearStart = $_POST['filterYearStart'] ?? null; 
$filterYearEnd = $_POST['filterYearEnd'] ?? null;

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

$stmt->bind_param("s", $idUser);
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

// Paso previo: cargar departamentos
$departamentos = [];
$resultDeptos = $mysqli_vacaciones->query("SELECT id_departamento, nombre FROM departamentos");
if ($resultDeptos) {
    while ($row = $resultDeptos->fetch_assoc()) {
        $departamentos[$row['id_departamento']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo: cargar puestos
$puestos = [];
$resultPuestos = $mysqli_intranet->query("SELECT id_archivo, nombre FROM puestos");
if ($resultPuestos) {
    while ($row = $resultPuestos->fetch_assoc()) {
        $puestos[$row['id_archivo']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo: cargar empleados con puesto y nombre completo para autorizadores
$empleados = [];
$sql = "SELECT id, puesto, nombre, apellido_paterno, apellido_materno FROM empleados";
$resultEmps = $mysqli_vacaciones->query($sql);
if ($resultEmps) {
    while ($row = $resultEmps->fetch_assoc()) {
        $nombreCompleto = trim(
            ($row['nombre'] ?? '') . ' ' .
            ($row['apellido_paterno'] ?? '') . ' ' .
            ($row['apellido_materno'] ?? '')
        );
        $empleados[$row['id']] = [
            'puesto' => $row['puesto'] ?? null,
            'nombre_completo' => $nombreCompleto
        ];
    }
}

// --------------------------------------------------------------------------------------
// --- CONFIGURACIÓN DE FILTROS PARA SOLO AUTORIZADAS ---
// --------------------------------------------------------------------------------------

// Ambas autorizaciones deben ser 'AUTORIZADA'
$condicionAutorizada = " AND (solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 = 'AUTORIZADA')";

$clausulaYear = "";
if (!empty($filterYearStart) && !empty($filterYearEnd)) {
    $clausulaYear = " AND CAST(YEAR(solicitud_date_create) AS UNSIGNED) BETWEEN " . (int)$filterYearStart . " AND " . (int)$filterYearEnd;
}

// --------------------------------------------------------------------------------------

$listaSolicitudes = [];

if ($puesto === 'Gerente de Recursos Humanos' or str_contains($puesto, 'Reclutador') ) {
   $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, correo, empresa, id_departamento 
                 FROM empleados 
                 WHERE (puesto LIKE '%Gerente%' OR puesto LIKE '%Director%') AND status_empleado ='Activo' ";
    $result = $mysqli_vacaciones->query($sqlUsers);
    if ($result) {
        $listaUserAutorizados = [];
        while ($row = $result->fetch_assoc()) {
            $nombreCompleto = trim(
                ($row['nombre'] ?? '') . ' ' .
                ($row['apellido_paterno'] ?? '') . ' ' .
                ($row['apellido_materno'] ?? '')
            );

            $listaUserAutorizados[] = [
                "id" => htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8'),
                "nombre_completo" => htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'),
                "puesto" => htmlspecialchars($row['puesto'] ?? '', ENT_QUOTES, 'UTF-8'),
                "correo" => htmlspecialchars($row['correo'] ?? '', ENT_QUOTES, 'UTF-8'),
                "empresa" => htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES, 'UTF-8'),
                "id_departamento" => htmlspecialchars($row['id_departamento'] ?? '', ENT_QUOTES, 'UTF-8')
            ];
        }
    }

    foreach ($listaUserAutorizados as $user) {
        $userId = $mysqli_solicitud->real_escape_string($user['id']);
        $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id= '$userId' " . $condicionAutorizada . $clausulaYear;
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

                // Departamento
                $nombreDepartamento = '';
                $deptoId = $user['id_departamento'] ?? '';
                if ($deptoId && isset($departamentos[$deptoId])) {
                    $nombreDepartamento = $departamentos[$deptoId];
                }

                // Autorizador 1
                $aut1NombreCompleto = '';
                $aut1Puesto = '';
                $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
                if ($aut1Id && isset($empleados[$aut1Id])) {
                    $aut1NombreCompleto = $empleados[$aut1Id]['nombre_completo'];
                    $aut1PuestoId = $empleados[$aut1Id]['puesto'];
                    $aut1Puesto = $puestos[$aut1PuestoId] ?? $aut1PuestoId;
                }

                // Autorizador 2 (Integrado de getMisSolicitudes)
                $aut2NombreCompleto = '';
                $aut2Puesto = '';
                $aut2Id = $solicitudBlindada['solicitud_autorizador2_id'] ?? '';
                if ($aut2Id && isset($empleados[$aut2Id])) {
                    $aut2NombreCompleto = $empleados[$aut2Id]['nombre_completo'];
                    $aut2PuestoId = $empleados[$aut2Id]['puesto'];
                    $aut2Puesto = $puestos[$aut2PuestoId] ?? $aut2PuestoId; 
                }

                $solicitudConUsuario = array_merge($solicitudBlindada, [
                    "usuario_id" => $user['id'],
                    "usuario_nombre_completo" => $user['nombre_completo'],
                    "usuario_puesto" => $user['puesto'],
                    "usuario_correo" => $user['correo'] ?? '',
                    "usuario_empresa" => $user['empresa'],
                    "usuario_id_departamento" => $user['id_departamento'],
                    "usuario_departamento_nombre" => $nombreDepartamento,
                    "solicitud_nombre_puesto" => $nombrePuesto,
                    "autorizador1_nombre_completo" => $aut1NombreCompleto,
                    "autorizador1_puesto" => $aut1Puesto,
                    "autorizador2_nombre_completo" => $aut2NombreCompleto,
                    "autorizador2_puesto" => $aut2Puesto
                ]);

                $listaSolicitudes[] = $solicitudConUsuario;
            }
        }
    }
} else {
    // Caso Directores / Jefes
    $sqlAuth = "SELECT id, clave, clave_autorizador FROM autoridad_departamental WHERE clave_autorizador = ? OR id = ?";
    $stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
    $stmtAuth->bind_param("ss", $autoridad, $autoridad);
    $stmtAuth->execute();
    $stmtAuth->bind_result($authId, $clave, $claveAutorizador);

    $listaGruposAutorizados = [];
    while ($stmtAuth->fetch()) {
        $listaGruposAutorizados[] = ["id" => $authId, "clave" => $clave, "clave_autorizador" => $claveAutorizador];
    }
    $stmtAuth->close();

    $listaUserAutorizados = [];
    foreach ($listaGruposAutorizados as $grupo) {
        $grupoClave = $mysqli_vacaciones->real_escape_string($grupo['id']);
        $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, correo, empresa, id_departamento FROM empleados WHERE id_autoridad = '$grupoClave' AND status_empleado ='Activo' ";
        $result = $mysqli_vacaciones->query($sqlUsers);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $nombreCompleto = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? '') . ' ' . ($row['apellido_materno'] ?? ''));
                $listaUserAutorizados[] = [
                    "id" => htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8'),
                    "nombre_completo" => htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'),
                    "puesto" => htmlspecialchars($row['puesto'] ?? '', ENT_QUOTES, 'UTF-8'),
                    "correo" => htmlspecialchars($row['correo'] ?? '', ENT_QUOTES, 'UTF-8'),
                    "empresa" => htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES, 'UTF-8'),
                    "id_departamento" => htmlspecialchars($row['id_departamento'] ?? '', ENT_QUOTES, 'UTF-8')
                ];
            }
        }
    }

    foreach ($listaUserAutorizados as $user) {
        $userId = $mysqli_solicitud->real_escape_string($user['id']);
        $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId' " . $condicionAutorizada . $clausulaYear;
        $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

        if ($resultSolicitudes) {
            while ($row = $resultSolicitudes->fetch_assoc()) {
                $solicitudBlindada = array_map(function($v){
                    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
                }, $row);
                
                $nombrePuesto = '';
                $puestoId = $solicitudBlindada['solicitud_puesto_id'] ?? '';
                if ($puestoId && isset($puestos[$puestoId])) {
                    $nombrePuesto = $puestos[$puestoId];
                }

                $nombreDepartamento = '';
                $deptoId = $user['id_departamento'] ?? '';
                if ($deptoId && isset($departamentos[$deptoId])) {
                    $nombreDepartamento = $departamentos[$deptoId];
                }

                // Autorizador 1
                $aut1NombreCompleto = '';
                $aut1Puesto = '';
                $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
                if ($aut1Id && isset($empleados[$aut1Id])) {
                    $aut1NombreCompleto = $empleados[$aut1Id]['nombre_completo'];
                    $aut1PuestoId = $empleados[$aut1Id]['puesto'];
                    $aut1Puesto = $puestos[$aut1PuestoId] ?? $aut1PuestoId;
                }

                // Autorizador 2 (Integrado de getMisSolicitudes)
                $aut2NombreCompleto = '';
                $aut2Puesto = '';
                $aut2Id = $solicitudBlindada['solicitud_autorizador2_id'] ?? '';
                if ($aut2Id && isset($empleados[$aut2Id])) {
                    $aut2NombreCompleto = $empleados[$aut2Id]['nombre_completo'];
                    $aut2PuestoId = $empleados[$aut2Id]['puesto'];
                    $aut2Puesto = $puestos[$aut2PuestoId] ?? $aut2PuestoId;
                }

                $solicitudConUsuario = array_merge($solicitudBlindada, [
                    "usuario_id" => $user['id'],
                    "usuario_nombre_completo" => $user['nombre_completo'],
                    "usuario_puesto" => $user['puesto'],
                    "usuario_correo" => $user['correo'] ?? '',
                    "usuario_empresa" => $user['empresa'],
                    "usuario_id_departamento" => $user['id_departamento'],
                    "usuario_departamento_nombre" => $nombreDepartamento,
                    "solicitud_nombre_puesto" => $nombrePuesto,
                    "autorizador1_nombre_completo" => $aut1NombreCompleto,
                    "autorizador1_puesto" => $aut1Puesto,
                    "autorizador2_nombre_completo" => $aut2NombreCompleto,
                    "autorizador2_puesto" => $aut2Puesto
                ]);

                $listaSolicitudes[] = $solicitudConUsuario;
            }
        }
    }
}

// Ordenar descendente por ID
usort($listaSolicitudes, function($a, $b) {
    $idA = (int)$a['solicitud_id'];
    $idB = (int)$b['solicitud_id'];
    if ($idA == $idB) return 0;
    return ($idA > $idB) ? -1 : 1;
});

header('Content-Type: application/json');
if (empty($listaSolicitudes)) {
    echo json_encode(["err" => false, "statusText" => "No se encontraron solicitudes.", "data" => []], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["err" => false, "statusText" => "Solicitudes obtenidas correctamente.", "data" => $listaSolicitudes], JSON_UNESCAPED_UNICODE);
}
?>