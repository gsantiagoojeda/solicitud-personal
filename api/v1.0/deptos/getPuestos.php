<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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
// $depto = "5";
// $puesto = "Director de Administración, Finanzas y Contabilida";
// $idUser = "296";
// $depto = $_POST['depto'];
$puesto = $_POST['puesto'];
$idUser = $_POST['id-user'];

// Paso 1: Obtener puesto y autoridad del usuario
$sqlUser = "SELECT id_autoridad FROM empleados WHERE id = ?";
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
// $stmt->bind_result($puesto, $autoridad);
$stmt->bind_result($autoridad);
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
$listaPuestos = [];

foreach ($listaDeptosAutorizados as $idDepto) {
    // 1. Consultamos la descripción en la base de datos de Intranet
    $sqlDesc = "SELECT id_archivo, id_puesto, descripcion, departamento_id 
                FROM descripcion_puestos 
                WHERE departamento_id = ?";
    
    $stmtDesc = $mysqli_intranet->prepare($sqlDesc);
    $stmtDesc->bind_param("i", $idDepto);
    $stmtDesc->execute();
    
    // Almacenamos el resultado para poder hacer otra consulta dentro del while si fuera necesario
    $stmtDesc->store_result();
    $stmtDesc->bind_result($id_archivo, $id_puesto, $descripcion, $departamento_id);

    while ($stmtDesc->fetch()) {
        
        // 2. Buscamos el nombre del puesto en la base de datos de Vacaciones
        $nombre_puesto = "No encontrado"; // Valor por defecto
        
        $sqlNom = "SELECT nombre_puesto FROM puestos WHERE id_puesto = ?";
        if ($stmtNom = $mysqli_vacaciones->prepare($sqlNom)) {
            $stmtNom->bind_param("i", $id_puesto);
            $stmtNom->execute();
            $stmtNom->bind_result($res_nombre);
            if ($stmtNom->fetch()) {
                $nombre_puesto = $res_nombre;
            }
            $stmtNom->close();
        }

        // 3. Guardamos todo en el array final
        $listaPuestos[] = [
            "id_archivo"      => $id_archivo,
            "id_puesto"       => $id_puesto,
            "nombre"          => $nombre_puesto,
            "descripcion"     => $descripcion,
            "departamento_id" => $departamento_id
        ];
    }
    $stmtDesc->close();
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $listaPuestos,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);

?>


