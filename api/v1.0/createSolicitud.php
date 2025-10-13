<?php
require "./conexion_solicitud.php";

header('Content-Type: application/json');

// Obtener datos de $_POST y asignar null si no existen
$puesto = $_POST['puesto'] ?? null;
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
$rolar = $_POST['rolar'] ?? null;
$solicitante = $_POST['solicitante'] ?? null;
$solicitantePuesto = $_POST['solicitante-puesto'] ?? null;

// Determinar autorizador y fecha si el solicitante es director
$autorizador1 = null;
$fechaAuth = null;
if (!empty($solicitantePuesto) && stripos($solicitantePuesto, "director") !== false) {
    $autorizador1 = $solicitante;
    $fechaAuth = date("Y-m-d H:i:s");
}

// Preparar query seguro
$sql = "
INSERT INTO sp_solicitud (
    solicitud_puesto_id,
    solicitud_espacio_trabajo,
    solicitud_espacio_trabajo_com,
    solicitud_mobiliario,
    solicitud_mobiliario_com,
    solicitud_equipo_computo,
    solicitud_equipo_computo_com,
    solicitud_herramientas,
    solicitud_herramientas_com,
    solicitud_compras_necesarias,
    solicitud_fecha_tentativa,
    solicitud_num_vacantes,
    solicitud_sexo,
    solicitud_estado_civil,
    solicitud_escolaridad,
    solicitud_edad_min,
    solicitud_edad_max,
    solicitud_experiencia,
    solicitud_conocimientos,
    solicitud_habilidades,
    solicitud_tools,
    solicitud_sueldo_id,
    solicitud_horario_id,
    solicitud_rolar,
    solicitud_solicitante_id,
    solicitud_autorizador1_id,
    solicitud_autorizacion1,
    solicitud_date_autorizacion1
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $mysqli_solicitud->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysql_solicitud->error
    ]);
    exit;
}

// Bind de parámetros (s: string, i: integer) - NULL se pasa como null
$stmt->bind_param(
    "ssssssssssssssssssssssssssss",
    $puesto,
    $rp1,
    $rp2,
    $rp3,
    $rp4,
    $trp1,
    $trp2,
    $trp3,
    $trp4,
    $compras,
    $fechaTentativa,
    $numVacantes,
    $sexo,
    $estadoCivil,
    $escolaridad,
    $edadMinima,
    $edadMaxima,
    $experiencia,
    $conocimientos,
    $habilidades,
    $tools,
    $sueldo,
    $horario,
    $rolar,
    $solicitante,
    $autorizador1,
    "Autorizada",
    $fechaAuth
);

// Ejecutar query
if ($stmt->execute()) {
    echo json_encode([
        "err" => false,
        "statusText" => "Datos guardados correctamente"
    ]);
} else {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al guardar datos: " . $stmt->error
    ]);
}

$stmt->close();
$mysql_solicitud->close();

