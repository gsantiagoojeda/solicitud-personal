<?php
require_once __DIR__ . '/enviar_correo.php';
require_once '../config/db_fader.php';    // Base interna
require_once '../config/data_fader.php';  // Base externa

$conexion_externa = $conexion; // Alias base externa

/**
 * Notificar aprobación de FADER
 *
 * @param string $folio Folio del FADER
 * @param string $tipo_aprobador Tipo de aprobador: "lider", "gerente", "director"
 * @param string $tipo_fader Tipo de fader: "abierto" o "cerrado"
 */
function notificarAprobacionFader($folio, $tipo_aprobador, $tipo_fader = "cerrado") {
    global $conexion_interna, $conexion_externa;

    // 🔹 Obtener datos base del FADER
    $stmt = $conexion_interna->prepare("
        SELECT id_usuario_fader, id_linea 
        FROM fader 
        WHERE folio_fader = ?
    ");
    $stmt->bind_param("s", $folio);
    $stmt->execute();
    $stmt->bind_result($id_creador, $id_linea);
    if (!$stmt->fetch()) {
        error_log("FADER_APROBACION: No se encontró FADER $folio");
        return;
    }
    $stmt->close();

    // Helper para obtener correo
    $getCorreo = function($idUsuarioExterno) use ($conexion_externa) {
        $correo = null;
        $stmt = $conexion_externa->prepare("SELECT correo FROM empleados WHERE id = ?");
        $stmt->bind_param("i", $idUsuarioExterno);
        $stmt->execute();
        $stmt->bind_result($correoBD);
        if ($stmt->fetch() && filter_var($correoBD, FILTER_VALIDATE_EMAIL)) {
            $correo = $correoBD;
        }
        $stmt->close();
        return $correo;
    };

    // Helper para destinatarios por rol
    $getUsuariosPorRol = function($rol, $id_linea = null) use ($conexion_interna, $getCorreo) {
        $correos = [];
        if ($id_linea) {
            $stmt = $conexion_interna->prepare("SELECT id_usuario_externo FROM usuarios_fader_ti WHERE rol = ? AND id_linea_responsable = ?");
            $stmt->bind_param("ii", $rol, $id_linea);
        } else {
            $stmt = $conexion_interna->prepare("SELECT id_usuario_externo FROM usuarios_fader_ti WHERE rol = ? LIMIT 1");
            $stmt->bind_param("i", $rol);
        }
        $stmt->execute();
        $stmt->bind_result($id_usuario);
        while ($stmt->fetch()) {
            $correo = $getCorreo($id_usuario);
            if ($correo) $correos[] = $correo;
        }
        $stmt->close();
        return $correos;
    };

    // 📌 Correos base
    $destinatarios = [];
    $correoCreador = $getCorreo($id_creador);
    if ($correoCreador) $destinatarios[] = $correoCreador;

    $destinatarios = array_merge($destinatarios, $getUsuariosPorRol(3, $id_linea)); // Líder

    if (in_array($tipo_aprobador, ["gerente", "director"])) {
        $destinatarios = array_merge($destinatarios, $getUsuariosPorRol(5, $id_linea)); // Gerente regional
    }
    if ($tipo_aprobador === "director") {
        $destinatarios = array_merge($destinatarios, $getUsuariosPorRol(2)); // Director comercial
    }

    // 📩 Contenido correo base
    $contenidoHTML = "
        <h2>FADER Aprobado</h2>
        <p>El FADER <b>$folio</b> fue aprobado por el <b>$tipo_aprobador</b>.</p>
        <p>Puedes dar seguimiento al FADER en el sistema.</p>
    ";
    enviarCorreoGenerico($folio, "FADER aprobado (#$folio)", $contenidoHTML, $destinatarios);

    // 📩 Correo extra dependiendo del aprobador
    if ($tipo_aprobador === "lider") {
        // Avisar al gerente regional (rol 5)
        $destGerente = $getUsuariosPorRol(5, $id_linea);
        if ($destGerente) {
            $msg = "<h2>Tienes un FADER pendiente</h2><p>Revisa el sistema para aprobar el folio <b>$folio</b>.</p>";
            enviarCorreoGenerico($folio, "FADER pendiente (#$folio)", $msg, $destGerente);
        }
    }
    elseif ($tipo_aprobador === "gerente") {
        // Avisar al director comercial (rol 2)
        $destDirector = $getUsuariosPorRol(2);
        if ($destDirector) {
            $msg = "<h2>Tienes un FADER pendiente</h2><p>Revisa el sistema para aprobar el folio <b>$folio</b>.</p>";
            enviarCorreoGenerico($folio, "FADER pendiente (#$folio)", $msg, $destDirector);
        }
    }
    elseif ($tipo_aprobador === "director") {
        // Avisar a cobranza (rol 6) solo si es abierto
        if ($tipo_fader === "abierto") {
            $destCobranza = $getUsuariosPorRol(6);
            if ($destCobranza) {
                $msg = "<h2>Tienes un FADER pendiente de llenar</h2><p>El folio <b>$folio</b> requiere tu atención.</p>";
                enviarCorreoGenerico($folio, "FADER pendiente de llenar (#$folio)", $msg, $destCobranza);
            }
        }
    }
}

/**
 * Enviar correo genérico con plantilla
 */
function enviarCorreoGenerico($folio, $asunto, $contenidoHTML, $destinatarios) {
    if (empty($destinatarios)) return;

    $template_path = __DIR__ . '/plantillas/fader_aprobado.html';
    $template = file_exists($template_path) 
        ? file_get_contents($template_path) 
        : "<html><body>{{CONTENIDO}}<br><a href='{{URL_INTRANET}}'>Ver FADER</a></body></html>";

    $url_intranet = "https://gpoalze.cloud/fader/index_fader.php?folio=$folio";
    $correoHTML = str_replace(['{{CONTENIDO}}','{{URL_INTRANET}}'], [$contenidoHTML, $url_intranet], $template);

    enviarCorreoFader($asunto, $destinatarios, $correoHTML);
}
