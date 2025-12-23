<?php
require "../conexion_solicitud.php";
require_once __DIR__ . '/../mail/enviar_correo.php';

header('Content-Type: application/json');

// --- 1. Obtener y Sanitizar Datos ---

$id = $_POST['id'] ?? null;
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
$sql;
if ($level == 1) {
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
    solicitud_date_autorizacion1 = ?
WHERE solicitud_id = ?
";
} else if ($level == 2) {
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
    solicitud_autorizador2_id = ?,
    solicitud_autorizacion2 = ?,
    solicitud_date_autorizacion2 = ?
WHERE solicitud_id = ?
";
}



$stmt = $mysqli_solicitud->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al preparar la consulta: " . $mysqli_solicitud->error,
        "postData" => $_POST
    ]);
    exit;
}


$stmt->bind_param(
    "ssssssssssssssssssssssssssss", 
    $rp1,
    $trp1,
    $rp2,
    $trp2,
    $rp3,
    $trp3,
    $rp4,
    $trp4,
    $compras,
    $fechaTentativa,
    $responsable,
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
    $autorizador,
    $status,
    $dateAuth,
    $id 
);

// --- 5. Ejecutar y Cerrar ---
if ($stmt->execute()) {
    echo json_encode([
        "err" => false,
        "statusText" => "Datos actualizados correctamente. Filas afectadas: " . $stmt->affected_rows,
        "postData" => $_POST
    ]);
    if($level ==2){
          $contenidoHTML = "
        <h2>SOLICITUD AUTORIZADA</h2>
        <p>Una nueva vacante fue aprobada para su reclutamiento. <b>$id</b>.</p>
        <p>Puedes dar seguimiento a las solicitudes en el sistema.</p>
    ";
    // $destinatario="reclutamiento@gpoalze.com";
   $destinatarios = ["gonzalo.santiago@etiroch.onmicrosoft.com", "lucio.zempoalteca@gpoalze.com"];

    $template_path = __DIR__ . '/../mail/solicitud_aprobada.html';
    $template = file_exists($template_path) 
        ? file_get_contents($template_path) 
        : "<html><body>{{CONTENIDO}}<br><a href='{{URL_INTRANET}}'>Ver Solicitudes</a></body></html>";

    $url_intranet = "https://gpoalze.cloud/solicitud-personal/menu.html";
    $correoHTML = str_replace(['{{CONTENIDO}}','{{URL_INTRANET}}'], [$contenidoHTML, $url_intranet], $template);
    $asunto="VACANTE AUTORIZADA";
    enviarCorreo($asunto, $destinatarios, $correoHTML);


    }
} else {
    echo json_encode([
        "err" => true,
        "statusText" => "Error al actualizar datos: " . $stmt->error
    ]);
}

$stmt->close();
$mysqli_solicitud->close();
?>