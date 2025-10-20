<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require "../conexion_intranet.php";
require "../conexion_vacaciones.php";

header('Content-Type: application/json');
$depto = "4";
$puesto = "Director de Almacenes, Tecnologia y Transformacion";
$idUser = "286";
// $depto = $_POST['depto'];
// $puesto = $_POST['puesto'];
// $idUser = $_POST['id-user'];

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

// Paso 3: Obtener departamentos de cada grupo autorizado
$listaDeptosAutorizados = [];

foreach ($listaGruposAutorizados as $grupo) {
    $grupoClave = $grupo['id'];

    $sqlDepto = "SELECT id_departamento FROM empleados WHERE id_autoridad = ?";
    $stmtDepto = $mysqli_vacaciones->prepare($sqlDepto);
    $stmtDepto->bind_param("i", $grupoClave);
    $stmtDepto->execute();
    $stmtDepto->bind_result($idDepto);

    while ($stmtDepto->fetch()) {
        // Agregar solo si no existe aún
        if (!in_array($idDepto, $listaDeptosAutorizados)) {
            $listaDeptosAutorizados[] = $idDepto;
        }
    }

    $stmtDepto->close();
}

// echo("deptos autorizados:");
// print_r($listaDeptosAutorizados);
// echo "<br>";
// Paso 4: Obtener puestos de cada departamento autorizado
$listaDeptos = [];
foreach ($listaDeptosAutorizados as $idDepto) {
    $sqlDepto = "SELECT *FROM departamentos WHERE departamento_id = ?";
    $stmtDepto = $mysqli_vacaciones->prepare($sqlDepto);
    $stmtDepto->bind_param("i", $idDepto);
    $stmtDepto->execute();
    $stmtDepto->bind_result($departamento_id,$nombre, $descripcion);

    while ($stmtPuesto->fetch()) {
        $listaDeptos[] = [
          "departamento_id" => $departamento_id,
            "nombre" => $nombre,
            "descripcion" => $descripcion
        ];
    }
    $stmtPuesto->close();
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $listaDeptos,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);

?>
