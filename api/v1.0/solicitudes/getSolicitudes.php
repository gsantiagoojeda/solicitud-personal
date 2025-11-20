<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";

$idUser = "3009"; // usuario actual
// $idUser = $_POST['user-id']; // usuario actual

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
                 WHERE id_autoridad = '$grupoClave'";
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

// Paso 4: Construir solicitudes
$listaSolicitudes = [];
foreach ($listaUserAutorizados as $user) {
    $userId = $mysqli_solicitud->real_escape_string($user['id']);
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId'";

// Lógica condicional para modificar la cláusula WHERE
if (strpos($puesto, 'Director') !== false) {
    // Si $puesto incluye 'Director', busca NULL o 'Rechazada' en solicitud_autorizacion1
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId' AND (solicitud_autorizacion1 IS NULL OR solicitud_autorizacion1 = 'Rechazada')";
} elseif ($puesto === 'Gerente de Recursos Humanos') {
    // Si $puesto es exactamente 'Gerente de Recursos Humanos', busca NULL o 'Rechazada' en solicitud_autorizacion2
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE (solicitud_autorizacion2 IS NULL OR solicitud_autorizacion2 = 'Rechazada')";
}
$resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);
echo "entre1";

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
        echo "entre2";
    }
}

print_r($listaSolicitudes);

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
    // Si quisieras orden ascendente (menor ID primero), sería:
    // return $idA - $idB;
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
