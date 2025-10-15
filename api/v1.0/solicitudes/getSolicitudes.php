<?php
// $sqlSolicitudes = "
//     SELECT s.*
//     FROM ti_solicitud_personal s
//     JOIN empleados e ON s.solicitud_solicitante_id = e.empleado_id
//     JOIN departamentos d ON e.departamento_id = d.departamento_id
//     WHERE e.departamento_id = ?
// ";

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";

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

$stmt->bind_param("i", $idUser);
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

    $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, empresa, id_departamento FROM empleados WHERE id_autoridad = ?";
    $stmtUsers = $mysqli_vacaciones->prepare($sqlUsers);
    $stmtUsers->bind_param("i", $grupoClave);
    $stmtUsers->execute();
    $stmtUsers->bind_result($idUsers);

    $stmtUsers->close();
}

// echo("deptos autorizados:");
// print_r($listaDeptosAutorizados);
// echo "<br>";
// Paso 4: Obtener solicitudes de cada usuario autorizado
$listaSolicitudes = [];

foreach ($listaUserAutorizados as $idUser) {
    $sql = "SELECT * FROM sp_solicitud WHERE user_id = $idUser";
    $result = $mysqli_intranet->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listaSolicitudes[] = $row; // ← ya contiene TODOS los campos
        }
    }
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $listaPuestos,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);

?>

