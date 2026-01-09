<?php
require_once __DIR__ . '/config_correo.php';

/**
 * Envía un correo usando PHPMailer
 *
 * @param string $asunto Asunto del correo
 * @param array $destinatarios Lista de correos válidos
 * @param string $contenidoHTML Contenido HTML del correo
 * @return bool True si se envió correctamente, false si hubo error
 */
function enviarCorreo($asunto, $destinatarios, $contenidoHTML) {
    $mail = getMailer();

    // Agregar destinatarios válidos
    foreach ($destinatarios as $correo) {
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($correo);
        }
    }

    // Agregar logo embebido si existe
    //$logo_path = __DIR__ . '/../img/alze.png';
    //<img src='$logo_src' alt='Logo' style='max-width:150px;'>
    //$logo_src = file_exists($logo_path) ? 'cid:logo_empresa' : 'https://gpoalze.cloud/img/alze.png';
    //if (file_exists($logo_path)) $mail->addEmbeddedImage($logo_path, 'logo_empresa', 'alze.png');

    // Plantilla HTML
   $html = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eeeeee; max-width: 600px;'>
        $contenidoHTML
        
        <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%'>
            <tr>
                <td style='border-top: 1px solid #dddddd; padding-top: 15px;'>
                    <p style='margin: 0; font-size: 12px; color: #777777; font-family: Arial, sans-serif; text-align: center;'>
                        Este es un correo automático de <b>GPOALZE Solicitud Personal</b>. Por favor, no responda a este mensaje.
                    </p>
                </td>
            </tr>
        </table>
    </div>";

    $mail->Subject = $asunto;
    $mail->Body    = $html;

    try {
        return $mail->send();
    } catch (Exception $e) {
        error_log("SOLICITUD_MAIL: Excepción PHPMailer: " . $e->getMessage());
        return false;
    }
}