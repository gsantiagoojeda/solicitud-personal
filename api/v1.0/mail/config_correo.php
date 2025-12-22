<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vacaciones/vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer(true);

    // Usar mail() del servidor GoDaddy
    $mail->isMail();
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // Remitente por defecto
    $mail->setFrom("notificaciones@gpoalze.cloud", "Solicitud Personal");
    $mail->isHTML(true);

    return $mail;
}