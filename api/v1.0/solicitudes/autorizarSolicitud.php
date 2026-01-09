<?php
require "../conexion_solicitud.php";
require_once __DIR__ . '/../mail/enviar_correo.php';

header('Content-Type: application/json');

// --- 1. Obtener y Sanitizar Datos ---

$id = $_POST['id'] ?? null;
$vacante = $_POST['vacante'] ?? null;
$solicitante = $_POST['solicitante'] ?? null;
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
<table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%' style='border: 1px solid #005a87; font-family: Arial, sans-serif;'>
    <tr>
        <td align='center' bgcolor='#005a87' style='padding: 10px;'>
            <h1 style='color: #ffffff; font-size: 20px; margin: 0; text-transform: uppercase;'>Solicitud Aprobada</h1>
        </td>
    </tr>
    
    <tr>
        <td style='padding: 20px 0;'>
            <table role='presentation' border='0' cellpadding='0' cellspacing='0' align='center'>
                <tr>
                    <td style='padding-right: 10px;'>
                        <img src='https://gpoalze.cloud/solicitud-personal/assets/GRUPO_ALZE.png' width='50' style='display: block; border: 0;'>
                    </td>
                    <td>
                        <h2 style='margin: 0; font-size: 24px;'>
                            <span style='background-color: #fff2a8; color: #000;'>VACANTE</span> <span style='color: #000;'>AUTORIZADA</span>
                        </h2>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td align='center' style='padding: 0 20px 20px 20px; color: #333333; font-size: 15px;'>
            <p style='margin: 10px 0;'>Una nueva vacante para <b>$vacante</b> fue aprobada para su reclutamiento.</p>
            <p style='margin: 10px 0;'>Solicitada por: <b>$solicitante</b></p>
            <p style='margin: 10px 0;'>Puedes dar seguimiento a las solicitudes en el sistema.</p>
            
            <br>
            <table role='presentation' border='0' cellpadding='0' cellspacing='0' align='center'>
                <tr>
                    <td bgcolor='#005a87' style='padding: 8px 15px;'>
                        <a href='#' style='color: #ffffff; text-decoration: none; font-weight: bold; font-size: 14px;'>VER SOLICITUD</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td bgcolor='#f9f9f9' style='padding: 15px; border-top: 1px solid #005a87;'>
            <p style='margin: 0; font-size: 12px; color: #555555; text-align: center; line-height: 1.4;'>
                Este es un correo automático del sistema de SOLICITUD PERSONAL<br>
                No responda a este mensaje.
            </p>
        </td>
    </tr>
</table>
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