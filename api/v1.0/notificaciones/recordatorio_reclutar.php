<?php
require "../conexion_solicitud.php";

// 1. Obtener los IDs pendientes
$sql = "SELECT id FROM sp_solicitud WHERE autorizador3 = ''";
$result = $mysqli_solicitud->query($sql);

$lista_ids = "";
$count = 0;

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Creamos un item de lista por cada ID
        $lista_ids .= "<li>Solicitud #<b>" . $row['id'] . "</b></li>";
        $count++;
    }
}

// Si no hay pendientes, podrías detener el proceso o enviar algo distinto
if ($count === 0) {
    die("No hay solicitudes pendientes para enviar por correo.");
}

// 2. Construir el contenido HTML dinámico
$contenidoHTML = "
    <div style='display: block; margin-bottom: 20px;'>
        <img src='https://gpoalze.cloud/solicitud-personal/assets/GRUPO_ALZE.png' style='vertical-align: middle; margin-right: 10px;' width='50'>
        <span style='font-size: 24px; font-weight: bold; color: #b22222; vertical-align: middle;'>PENDIENTES DE AUTORIZACIÓN</span>
    </div>

    <p>Hola, tienes <b>$count</b> solicitudes esperando tu validación (Autorizador 3).</p>
    
    <p>Los folios pendientes son:</p>
    <ul>
        $lista_ids
    </ul>

    <p>Por favor, ingresa al sistema para darles seguimiento y completar el proceso.</p>
";

// 3. Configuración del envío
$destinatarios = ["gonzalo.santiago@etiroch.onmicrosoft.com", "lucio.zempoalteca@gpoalze.com"];
$asunto = "RECORDATORIO: $count Solicitudes Pendientes de Autorizar";
$url_intranet = "https://gpoalze.cloud/solicitud-personal/menu.html";

// 4. Cargar Template y Reemplazar
$template_path = __DIR__ . '/../mail/solicitud_aprobada.html';
$template = file_exists($template_path) 
    ? file_get_contents($template_path) 
    : "<html><body style='font-family: Arial, sans-serif;'>{{CONTENIDO}}<br><br><a href='{{URL_INTRANET}}' style='background-color: #006432; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Solicitudes en Intranet</a></body></html>";

$correoHTML = str_replace(['{{CONTENIDO}}','{{URL_INTRANET}}'], [$contenidoHTML, $url_intranet], $template);

// 5. Enviar
enviarCorreo($asunto, $destinatarios, $correoHTML);

echo "Correo enviado con éxito avisando de $count pendientes.";

