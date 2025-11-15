<?php
require "../conexion_solicitud.php";

header('Content-Type: application/json');

// --- 1. Obtener y Sanitizar Datos ---
// Obtener datos de $_POST y asignar null si no existen (Uso de ?? para coalescing)
$id = $_POST['id'] ?? null; // ID es CRUCIAL para el WHERE
$rp1 = $_POST['rp1'] ?? null;
$rp2 = $_POST['rp2'] ?? null;
$rp3 = $_POST['rp3'] ?? null;
$rp4 = $_POST['rp4'] ?? null;
$trp1 = $_POST['trp1'] ?? null;
$trp2 = $_POST['trp2'] ?? null;
$trp3 = $_POST['trp3'] ?? null;
$trp4 = $_POST['trp4'] ?? null;
$compras = $_POST['compras'] ?? null;
$fechaTentativa = $_POST['fechaTentativa'] ?? null;
$responsable = $_POST['responsable'] ?? null;
$numVacantes = $_POST['numVacantes'] ?? null;
$sexo = $_POST['sexo'] ?? null;
$estadoCivil = $_POST['estadoCivil'] ?? null;
$escolaridad = $_POST['escolaridad'] ?? null;
$edadMinima = $_POST['edadMinima'] ?? null;
$edadMaxima = $_POST['edadMaxima'] ?? null;
$experiencia = $_POST['experiencia'] ?? null;
$conocimientos = $_POST['conocimientos'] ?? null;
$habilidades = $_POST['habilidades'] ?? null;
$tools = $_POST['tools'] ?? null;
$sueldo = $_POST['sueldo'] ?? null;
$horario = $_POST['horario'] ?? null;
// Convertir 'si'/'no' a booleano (1/0) o mantener null.
// El operador ternario anidado garantiza que si no existe, sea null, sino, 1 o 0.
$rolar = isset($_POST['rolar']) ? ($_POST['rolar'] === 'si' ? 1 : 0) : null;

// Parámetros de autorización
$autorizador = $_POST['autorizador'] ?? null;
$status = $_POST['status'] ?? null;
$level = $_POST['level'] ?? null;

$dateAuth = date("Y-m-d H:i:s");

// --- 2. Lógica de Asignación de Autorización ---
$autorizador1 = null;
$status1 = null;
$date1 = null;
$autorizador2 = null;
$status2 = null;
$date2 = null;

if ($level == 1) {
    // Si level es 1, actualiza la autorización 1
    $autorizador1 = $autorizador;
    $status1 = $status;
    $date1 = $dateAuth;
} else if ($level == 2) {
    // Si level es 2, actualiza la autorización 2
    $autorizador2 = $autorizador;
    $status2 = $status;
    $date2 = $dateAuth;
}

$sql = "
UPDATE sp_solicitud SET
    solicitud_espacio_trabajo = ?,
    solicitud_espacio_trabajo_com = ?,
    solicitud_mobiliario = ?,
    solicitud_mobiliario_com = ?,
    solicitud_equipo_computo = ?,
    solicitud_equipo_computo_com = ?,
    solicitud_herramientas = ?,
    solicitud_herramientas_com = ?,
    solicitud_compras_necesarias = ?,
    solicitud_fecha_tentativa = ?,
    solicitud_responsable = ?,
    solicitud_num_vacantes = ?,
    solicitud_sexo = ?,
    solicitud_estado_civil = ?,
    solicitud_escolaridad = ?,
    solicitud_edad_min = ?,
    solicitud_edad_max = ?,
    solicitud_experiencia = ?,
    solicitud_conocimientos = ?,
    solicitud_habilidades = ?,
    solicitud_tools = ?,
    solicitud_sueldo_id = ?,
    solicitud_horario_id = ?,
    solicitud_rolar = ?,
    solicitud_autorizador1_id = ?,
    solicitud_autorizacion1 = ?,
    solicitud_date_autorizacion1 = ?,
    solicitud_autorizador2_id = ?,
    solicitud_autorizacion2 = ?,
    solicitud_date_autorizacion2 = ?
WHERE solicitud_id = ?
";

$stmt = $mysqli_solicitud->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_solicitud->error,
        "postData" => $_POST
    ]);
    exit;
}

// --- 4. Bind de Parámetros Corregido ---
// 31 parámetros para SET + 1 parámetro para WHERE = 32 parámetros.
// El orden debe coincidir exactamente con el orden de los '?' en el SET, y el último '?' es el ID.
// Asumimos 's' para casi todo por seguridad, y 'i' para números enteros (como numVacantes, id's).
// NOTA: Es importante que el tipo de datos (s/i) coincida con el tipo de la columna en tu BD.
// He asumido 's' para la mayoría y 'i' para los campos que parecen ser IDs/números.

$stmt->bind_param(
    "ssssssssssssssssssssssssssssssss", 
    $rp1,
    $trp1, // 2. solicitud_espacio_trabajo_com
    $rp2, // 3. solicitud_mobiliario
    $trp2, // 4. solicitud_mobiliario_com
    $rp3, // 5. solicitud_equipo_computo
    $trp3, // 6. solicitud_equipo_computo_com
    $rp4, // 7. solicitud_herramientas
    $trp4, // 8. solicitud_herramientas_com
    $compras, // 9. solicitud_compras_necesarias
    $fechaTentativa, // 10. solicitud_fecha_tentativa
    $responsable, // 11. solicitud_responsable
    $numVacantes, // 12. solicitud_num_vacantes (i)
    $sexo, // 13. solicitud_sexo
    $estadoCivil, // 14. solicitud_estado_civil
    $escolaridad, // 15. solicitud_escolaridad
    $edadMinima, // 16. solicitud_edad_min (i)
    $edadMaxima, // 17. solicitud_edad_max (i)
    $experiencia, // 18. solicitud_experiencia
    $conocimientos, // 19. solicitud_conocimientos
    $habilidades, // 20. solicitud_habilidades
    $tools, // 21. solicitud_tools
    $sueldo, // 22. solicitud_sueldo_id (i)
    $horario, // 23. solicitud_horario_id (i)
    $rolar, // 24. solicitud_rolar (i - 1/0)
    $autorizador1, // 25. solicitud_autorizador1_id (i)
    $status1, // 26. solicitud_autorizacion1 (s)
    $date1, // 27. solicitud_date_autorizacion1 (s)
    $autorizador2, // 28. solicitud_autorizador2_id (i)
    $status2, // 29. solicitud_autorizacion2 (s)
    $date2, // 30. solicitud_date_autorizacion2 (s)
    $id // 31. ID para el WHERE (i)
);

// --- 5. Ejecutar y Cerrar ---
if ($stmt->execute()) {
    echo json_encode([
        "err" => false,
        "statusText" => "Datos actualizados correctamente. Filas afectadas: " . $stmt->affected_rows,
        "postData" => $_POST
    ]);
} else {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al actualizar datos: " . $stmt->error
    ]);
}

$stmt->close();
$mysqli_solicitud->close();
?>