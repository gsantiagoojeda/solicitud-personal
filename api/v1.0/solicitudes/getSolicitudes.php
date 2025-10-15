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
    $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, empresa, id_departamento 
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
    "empresa" => htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES, 'UTF-8'),
    "id_departamento" => htmlspecialchars($row['id_departamento'] ?? '', ENT_QUOTES, 'UTF-8')
];

        }
    }
}

// Paso 4: Obtener solicitudes de cada usuario autorizado
$// Paso 4: Obtener solicitudes de cada usuario autorizado con datos del usuario
$listaSolicitudes = [];
foreach ($listaUserAutorizados as $user) {
    $userId = $mysqli_solicitud->real_escape_string($user['id']); // solo el ID
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id = '$userId'";
    $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

    if ($resultSolicitudes) {
        while ($row = $resultSolicitudes->fetch_assoc()) {
            // Blindaje de campos de la solicitud
            $solicitudBlindada = array_map(function($v){
                return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
            }, $row);

            // Agregar los datos del usuario autorizado
            $solicitudConUsuario = array_merge($solicitudBlindada, [
                "usuario_id" => $user['id'],
                "usuario_nombre" => $user['nombre'],
                "usuario_apellido_paterno" => $user['apellido_paterno'],
                "usuario_apellido_materno" => $user['apellido_materno'],
                "usuario_puesto" => $user['puesto'],
                "usuario_empresa" => $user['empresa'],
                "usuario_id_departamento" => $user['id_departamento']
            ]);

            $listaSolicitudes[] = $solicitudConUsuario;
        }
    }
}

?>
