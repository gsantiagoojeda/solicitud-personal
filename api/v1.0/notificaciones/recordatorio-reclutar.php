<?php
require "../conexion_solicitud.php";
require_once __DIR__ . '/../mail/enviar_correo.php';

// 1. Obtener los IDs pendientes
$sql = "SELECT solicitud_id FROM sp_solicitud WHERE solicitud_autorizador3 IS NULL AND solicitud_autorizador2_id IS NOT NULL";
$result = $mysqli_solicitud->query($sql);

$lista_ids = "";
$count = 0;

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Creamos un item de lista por cada ID
        $lista_ids .= "<li>Solicitud #<b>" . $row['solicitud_id'] . "</b></li>";
        $count++;
    }
}

// Si no hay pendientes, podrías detener el proceso o enviar algo distinto
if ($count === 0) {
    die("No hay solicitudes pendientes para enviar por correo.");
}

// 2. Construir el contenido HTML dinámico
$contenidoHTML = '
<div style="width: 100%; margin-bottom: 20px;">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100%;">
            <tr>
                <td align="left" width="100" style="width: 100px !important;">
                    <img src="https://gpoalze.cloud/solicitud-personal/assets/GRUPO_ALZE.png" 
                         alt="Logo" 
                         width="100" 
                         style="display: block; width: 100px !important; min-width: 100px; max-width: 100px; border: 0;">
                </td>
                
                <td align="center" style="padding-right: 50px;">
                    <span style="font-size: 24px; font-weight: bold; color: #b22222; font-family: Arial, sans-serif;">
                        PENDIENTES DE AUTORIZACIÓN
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <p>Hola, tienes <b>' . $count . '</b> solicitudes esperando reclutamiento.</p>
    
  

    <p>Por favor, ingresa al sistema para darles seguimiento y completar el proceso.</p>
';
// 3. Configuración del envío
$destinatarios = ["gonzalo.santiago@etiroch.onmicrosoft.com", "lucio.zempoalteca@gpoalze.com"];
$asunto = "RECORDATORIO: $count Solicitudes Pendientes de Autorizar";
$url_intranet = "https://gpoalze.cloud/solicitud-personal/menu.html";

// 4. Cargar Template y Reemplazar
$template_path = __DIR__ . '/../mail/solicitud_reclutar.html';
$template = file_exists($template_path) 
    ? file_get_contents($template_path) 
    : "<html><body style='font-family: Arial, sans-serif;'>{{CONTENIDO}}<br><br><a href='{{URL_INTRANET}}' style='background-color: #006432; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Solicitudes en Intranet</a></body></html>";

$correoHTML = str_replace(['{{CONTENIDO}}','{{URL_INTRANET}}'], [$contenidoHTML, $url_intranet], $template);

// 5. Enviar
enviarCorreo($asunto, $destinatarios, $correoHTML, "SOLICITUDES POR RECLUTAR");

echo "Correo enviado con éxito avisando de $count pendientes.";

