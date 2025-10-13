<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "../conexion_intranet.php";
require "../conexion_vacaciones.php";

header('Content-Type: application/json');

// Validar que 'depto' esté presente y sea un número
// if (!isset($_POST['depto']) || !is_numeric($_POST['depto'])) {
//     echo json_encode([
//         "Puestos" => [],
//         "err" => true,
//         "statusText" => "ID de departamento inválido o no proporcionado"
//     ]);
//     exit;
// }

// Convertir a entero para mayor seguridad
$depto = "5";
$puesto = "Director de Administración, Finanzas y Contabilida";
$idUser = "296";
// $depto = (int)$_POST['depto'];
// $puesto = $_POST['puesto'];
// $idUser = (int)$_POST['id-user'];

if (stripos($puesto, 'Director') !== false) {//Contiene la palabra 'Director'


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

echo "mi clave autoridad es: $autoridad";

// Paso 2: Obtener grupos autorizados
$sqlAuth = "SELECT id, clave, clave_autorizador FROM autoridad_departamental WHERE clave_autorizador = ?";
$stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
$stmtAuth->bind_param("i", $autoridad);
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

print_r($listaGruposAutorizados);

// Paso 3: Obtener departamentos de cada grupo autorizado
$listaDeptosAutorizados = [];
foreach ($listaGruposAutorizados as $grupo) {
    $grupoClave = $grupo['id'];

    $sqlDepto = "SELECT id_departamento FROM empleados WHERE id_autoridad = ? LIMIT 1";
    $stmtDepto = $mysqli_vacaciones->prepare($sqlDepto);
    $stmtDepto->bind_param("i", $grupoClave);
    $stmtDepto->execute();
    $stmtDepto->bind_result($idDepto);
    if ($stmtDepto->fetch()) {
        $listaDeptosAutorizados[] = $idDepto;
    }
    $stmtDepto->close();
}

print_r($listaDeptosAutorizados);
// Paso 4: Obtener puestos de cada departamento autorizado
$listaPuestos = [];
foreach ($listaDeptosAutorizados as $idDepto) {
    $sqlPuesto = "SELECT id_archivo, nombre, descripcion, departamento_id FROM puestos WHERE departamento_id = ?";
    $stmtPuesto = $mysqli_intranet->prepare($sqlPuesto);
    $stmtPuesto->bind_param("i", $idDepto);
    $stmtPuesto->execute();
    $stmtPuesto->bind_result($id_archivo, $nombre, $descripcion, $departamento_id);

    while ($stmtPuesto->fetch()) {
        $listaPuestos[] = [
            "id_archivo" => $id_archivo,
            "nombre" => $nombre,
            "descripcion" => $descripcion,
            "departamento_id" => $departamento_id
        ];
    }
    $stmtPuesto->close();
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $listaPuestos,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);


} else {//"No contiene la palabra 'Director'
  
 $stmt = $mysqli_intranet->prepare("SELECT id_archivo, nombre, departamento_id FROM puestos WHERE departamento_id = ?");
if (!$stmt) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_intranet->error
    ]);
    exit;
}

$stmt->bind_param("i", $depto);

if (!$stmt->execute()) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "Error al ejecutar la consulta: " . $stmt->error
    ]);
    $stmt->close();
    exit;
}

// En lugar de get_result
$stmt->bind_result($id_archivo, $nombre, $departamento_id);

$array = [];
while ($stmt->fetch()) {
    $array[] = [
        "id_archivo" => $id_archivo,
        "nombre" => $nombre,
        "departamento_id" => $departamento_id
    ];
}

$stmt->close();

echo json_encode([
    "Puestos" => $array,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);

}// end if(stripos)
?>


