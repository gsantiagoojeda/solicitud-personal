<?php
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
$depto = "18";
$puesto = "Gerente de Tecnologias de la Informacion y Transformacion Digital";
$idUser = "264";
// $depto = (int)$_POST['depto'];
// $puesto = $_POST['puesto'];
// $idUser = (int)$_POST['id-user'];

if (stripos($texto, 'Director') !== false) {//Contiene la palabra 'Director'
  $sqlUser = "SELECT puesto, id_autoridad FROM empleados WHERE id = ?";

$stmt = $mysql_vacaciones->prepare($sqlUser);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "status" => "Error al preparar la consulta SQL"
    ]);
    exit;
}

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
  echo json_encode([
    "err" => true,
    "status" => "Usuario no encontrado"
  ]);
  $stmt->close();
  $mysql_vacaciones->close();
} 

$puesto=$row['puesto'];
$autoridad=$row['id_autoridad'];

$sqlAuth =  "SELECT id, clave, clave_autorizador 
            FROM autoridad_departamental 
            WHERE clave_autorizador = '$autoridad' ";

    $resultAuth = $mysql_vacaciones->query($sqlAuth);
    $listaGruposAutorizados = $resultAuth->fetch_all(MYSQLI_ASSOC);

// Paso 2: Iterar sobre los grupos que autorizó y obtener los departamentos
$listaDeptosAutorizados = [];
foreach ($listaGruposAutorizados as $grupo) {
  $grupoClave = $grupo['id'];
  
  $sqlDepto = "SELECT id_departamento FROM empleados WHERE id_autoridad = '$grupoClave' LIMIT 1";
  $resultDepto = $mysql_vacaciones->query($sqlDepto);
  $deptoRow = $resultDepto->fetch_assoc(); // solo un registro
  $idDepto = $deptoRow['id_departamento'];
  $listaDeptosAutorizados[] = $idDepto;
}

// Paso 3: Iterar sobre losdepartamentos que autorizó y obtener los puestos
$listaPuestos = [];
     foreach ($listaDeptosAutorizados as $depto){
       $deptoClave = $grupo['id'];
       $sqlPuesto =  "SELECT id_archivo, nombre, descripcion, departamento_id FROM autoridad_departamental 
            WHERE departamento_id = '$deptoClave' ";
        $resultPuesto = $mysql_vacaciones->query($sqlPuesto);
      $listaPuestos = $resultPuesto->fetch_all(MYSQLI_ASSOC);    
     }

// Respuesta exitosa
echo json_encode([
    "Puestos" => $listaPuestos,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);


} else {//"No contiene la palabra 'Director'

// Preparar la consulta de forma segura
$stmt = $mysqli_vacaciones->prepare("SELECT * FROM puestos WHERE departamento_id = ?");
if (!$stmt) {
    echo json_encode([
        "Puestos" => [],
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_vacaciones->error
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
    exit;
}

$resultPuestos = $stmt->get_result();

$array = [];
while ($rowPuestos = $resultPuestos->fetch_assoc()) {
    $array[] = $rowPuestos;
}

// Respuesta exitosa
echo json_encode([
    "Puestos" => $array,
    "err" => false,
    "statusText" => "Consulta exitosa"
]);

}// end if(stripos)
?>


