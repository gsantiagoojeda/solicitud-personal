<?php
// $sqlSolicitudes = "
//     SELECT s.*
//     FROM ti_solicitud_personal s
//     JOIN empleados e ON s.solicitud_solicitante_id = e.empleado_id
//     JOIN departamentos d ON e.departamento_id = d.departamento_id
//     WHERE e.departamento_id = ?
// ";

  require "./conexion_solicitud.php";

$id_user = intval($_POST['user-id']);

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
    $listaGruposAutorizados = $resultAuth>fetch_all(MYSQLI_ASSOC);

     // Paso 2: Iterar sobre los grupos que autorizó y verificar los empleados
    $listaPersonasAutorizadas = [];
    $idsAgregados = []; // Para evitar repetidos

    foreach ($listaGruposAutorizados as $grupo) {
        $grupoClave = $grupo['id'];

        // Obtener empleados del grupo autorizado
$sqlEmpleados = "
    SELECT e.id, e.nombre, e.apellido_paterno, e.apellido_materno, e.correo, e.puesto
    FROM empleados e
    INNER JOIN autoridad_departamental a ON e.id_autoridad = a.id
    WHERE a.id = ?
      AND e.status_empleado = 'Activo'
      AND LOWER(e.puesto) LIKE '%gerente%'
";


        $resultadoEmpleados = $conexion->query($sqlEmpleados);
        $empleados = $resultadoEmpleados->fetch_all(MYSQLI_ASSOC);

        $empleadosTipo2 = [];
        $empleadosTodos = [];

       // echo "Validando grupo: $grupoClave<br/>";
        
foreach ($empleados as $empleado) {
    if ($empleado['id'] == $idUser || in_array($empleado['id'], $idsAgregados)) {
        continue; // excluirte a ti mismo y evitar repetidos
    }

    // Registrar como ya agregado
    $idsAgregados[] = $empleado['id'];

    if ($empleado['tipo_usuario'] == '2' && $grupoClave != $miGrupo) {
        // Si es autorizador y NO es de tu grupo, lo metemos como autorizador externo
        $empleadosTipo2[] = $empleado;
    } else {
        // Si no es autorizador, lo consideramos "empleado general"
        $empleadosTodos[] = $empleado;
    }
}

        
        // Aplicar la lógica de selección
        if (!empty($empleadosTipo2)) {
            $listaPersonasAutorizadas = array_merge($listaPersonasAutorizadas, $empleadosTipo2);
        } else {
            $listaPersonasAutorizadas = array_merge($listaPersonasAutorizadas, $empleadosTodos);
        } 
    }

