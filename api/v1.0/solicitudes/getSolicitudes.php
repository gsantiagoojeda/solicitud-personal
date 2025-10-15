<?php
// $sqlSolicitudes = "
//     SELECT s.*
//     FROM ti_solicitud_personal s
//     JOIN empleados e ON s.solicitud_solicitante_id = e.empleado_id
//     JOIN departamentos d ON e.departamento_id = d.departamento_id
//     WHERE e.departamento_id = ?
// ";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";

// $idUser = $_POST['id-user'];
$idUser = "E216";

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

// bind_result en lugar de get_result
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

// echo "mi clave autoridad es: $autoridad";
// echo "<br>";

// Paso 2: Obtener grupos autorizados
$sqlAuth = "SELECT id, clave, clave_autorizador 
            FROM autoridad_departamental 
            WHERE clave_autorizador = ? OR id = ?";
$stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
$stmtAuth->bind_param("ii", $autoridad, $autoridad);
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

// echo("grupos autorizados:");
// print_r($listaGruposAutorizados);
// echo "<br>";

// Paso 3: Obtener usuarios de cada grupo autorizado
$listaUserAutorizados = [];

foreach ($listaGruposAutorizados as $grupo) {
    $grupoClave = $grupo['id'];

    $grupoClave = $mysqli_vacaciones->real_escape_string($grupoClave);
    $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, empresa, id_departamento 
                 FROM empleados 
                 WHERE id_autoridad = '$grupoClave'";

    $result = $mysqli_vacaciones->query($sqlUsers);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listaUserAutorizados[] = [
                "id" => htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'),
                "nombre" => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'),
                "apellido_paterno" => htmlspecialchars($row['apellido_paterno'], ENT_QUOTES, 'UTF-8'),
                "apellido_materno" => htmlspecialchars($row['apellido_materno'], ENT_QUOTES, 'UTF-8'),
                "puesto" => htmlspecialchars($row['puesto'], ENT_QUOTES, 'UTF-8'),
                "empresa" => htmlspecialchars($row['empresa'], ENT_QUOTES, 'UTF-8'),
                "id_departamento" => htmlspecialchars($row['id_departamento'], ENT_QUOTES, 'UTF-8')
            ];
        }
    }
}


// echo("deptos autorizados:");
// print_r($listaDeptosAutorizados);
// echo "<br>";
// Paso 4: Obtener solicitudes de cada usuario autorizado
$listaSolicitudes = [];
foreach ($listaUserAutorizados as $user) {

    $idUser = $mysqli_solicitud->real_escape_string($idser);
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE user_id = '$idUser'";
    $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);
    if ($resultSolcitudes) {
        while ($row = $result->fetch_assoc()) {
            $listaSolicitudes[] = $row;
        }
    }
}

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

