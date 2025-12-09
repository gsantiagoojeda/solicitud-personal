<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";


$idUser = $_POST['user-id']; // usuario actual

$filterAuth = $_POST['filterAuth'] ?? 'false'; 
$filterPend = $_POST['filterPend'] ?? 'false'; 
$filterRech = $_POST['filterRech'] ?? 'false'; 
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

// Paso previo: cargar empleados con puesto y nombre completo para autorizador1
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
// --- NUEVO: CONSTRUCCIÓN DINÁMICA DE LOS FILTROS DE ESTADO Y AÑO ---
// --------------------------------------------------------------------------------------

$filtrosStatus = [];

// CONDICIÓN PENDIENTE (Adaptada a la lógica de getSolicitudes.php)
if ($filterPend === "true") {
    if ($puesto === 'Gerente de Recursos Humanos') {
        // RH: Solicitud 1 Autorizada Y Solicitud 2 es NULL (PENDIENTE para RH)
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Autorizada' AND solicitud_autorizacion2 IS NULL)";
    } else {
        // Director/Jefe: Solicitud 1 es NULL (PENDIENTE para Director/Jefe)
        $filtrosStatus[] = "(solicitud_autorizacion1 IS NULL)";
    }
}

// CONDICIÓN RECHAZADA (Adaptada a la lógica de getSolicitudes.php)
if ($filterRech === "true") {
    if ($puesto === 'Gerente de Recursos Humanos') {
        // RH: Solicitud 1 Autorizada Y Solicitud 2 es 'Rechazada' (RECHAZADA por RH)
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Autorizada' AND solicitud_autorizacion2 = 'Rechazada')";
    } else {
        // Director/Jefe: Solicitud 1 es 'Rechazada' (RECHAZADA por Director/Jefe)
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Rechazada')";
    }
}

$clausulaStatus = "";
if (!empty($filtrosStatus)) {
    // Si al menos un filtro está activo, se construye el filtro OR normal.
    $clausulaStatus = " AND (" . implode(" OR ", $filtrosStatus) . ")";
} else {
    // *** CORRECCIÓN: Si NINGÚN filtro está activo, no devolver nada.
    $clausulaStatus = " AND (1 = 0)"; 
}

// Filtros de Rango de Año (Fecha)
$clausulaYear = "";
if (!empty($filterYearStart) && !empty($filterYearEnd)) {
    // Usamos YEAR() para extraer el año del TIMESTAMP y BETWEEN para el rango.
    $clausulaYear = " AND CAST(YEAR(solicitud_date_create) AS UNSIGNED) BETWEEN " . (int)$filterYearStart . " AND " . (int)$filterYearEnd;
}

// --------------------------------------------------------------------------------------
// --- FIN DE CONSTRUCCIÓN DINÁMICA DE LOS FILTROS ---
// --------------------------------------------------------------------------------------


// Paso 4: Construir solicitudes
$listaSolicitudes = [];

if ($puesto === 'Gerente de Recursos Humanos') {
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
    // Si $puesto es exactamente 'Gerente de Recursos Humanos', usa la lógica dinámica de filtros
    foreach ($listaUserAutorizados as $user) {
        $userId = $mysqli_solicitud->real_escape_string($user['id']);
        
        // ** Aplicación de los filtros dinámicos **
        // Se mantiene la condición 'solicitud_autorizacion1 = Autorizada' porque es el primer paso
        // para que la solicitud llegue a RH, y se combina con los filtros de estado/año.
        $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id= '$userId' AND (solicitud_autorizacion1 ='Autorizada')" . $clausulaStatus . $clausulaYear;

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

                // Autorizador1: nombre completo y puesto
                $aut1NombreCompleto = '';
                $aut1Puesto = '';
                $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
                if ($aut1Id && isset($empleados[$aut1Id])) {
                    $aut1NombreCompleto = $empleados[$aut1Id]['nombre_completo'];
                    $aut1Puesto = $empleados[$aut1Id]['puesto'];
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
                    "autorizador1_puesto" => $aut1Puesto
                ]);

                $listaSolicitudes[] = $solicitudConUsuario;
            }
        }
    }
}else{
  

// Paso 2: Obtener grupos autorizados
$sqlAuth = "SELECT id, clave, clave_autorizador 
            FROM autoridad_departamental 
            WHERE clave_autorizador = ? OR id = ?";
$stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
$stmtAuth->bind_param("ss", $autoridad, $autoridad);
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
                 WHERE id_autoridad = '$grupoClave' AND status_empleado ='Activo' ";
    $result = $mysqli_vacaciones->query($sqlUsers);
    if ($result) {
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
}
foreach ($listaUserAutorizados as $user) {
    $userId = $mysqli_solicitud->real_escape_string($user['id']);
 
    // ** Aplicación de los filtros dinámicos **
    // Si $puesto incluye 'Director', busca NULL o 'Rechazada' en solicitud_autorizacion1
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId' " . $clausulaStatus . $clausulaYear;

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

            // Autorizador1: nombre completo y puesto
            $aut1NombreCompleto = '';
            $aut1Puesto = '';
            $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
            if ($aut1Id && isset($empleados[$aut1Id])) {
                $aut1NombreCompleto = $empleados[$aut1Id]['nombre_completo'];
                $aut1Puesto = $empleados[$aut1Id]['puesto'];
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
                "autorizador1_puesto" => $aut1Puesto
            ]);

            $listaSolicitudes[] = $solicitudConUsuario;
        }
    }
}
}

// Ordenar el array $listaSolicitudes por 'solicitud_id' (Descendente por defecto)
usort($listaSolicitudes, function($a, $b) {
    // Convierte a entero para asegurar la comparación numérica
    $idA = (int)$a['solicitud_id'];
    $idB = (int)$b['solicitud_id'];
    
    // Para orden descendente (mayor ID primero):
    if ($idA == $idB) {
        return 0;
    }
    return ($idA > $idB) ? -1 : 1;
});

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