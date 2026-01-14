<?php
require "../conexion_solicitud.php";
require "../conexion_vacaciones.php";
require_once __DIR__ . '/../mail/enviar_correo.php';

// 1. Obtener solicitudes donde el Nivel 1 está pendiente (solicitud_autorizador1_id IS NULL)
$sqlPendientes = "SELECT solicitud_id, solicitud_solicitante_id FROM sp_solicitud WHERE solicitud_autorizador1_id IS NULL";
$resPendientes = $mysqli_solicitud->query($sqlPendientes);

if (!$resPendientes || $resPendientes->num_rows === 0) {
    die("No hay solicitudes pendientes de autorización Nivel 1.");
}

// 2. Agrupar solicitudes por el autorizador que les corresponde
// Usaremos un array: $pendientesPorAutorizador[correo_autorizador][] = id_solicitud
$pendientesPorAutorizador = [];

while ($row = $resPendientes->fetch_assoc()) {
    $solicitudId = $row['solicitud_id'];
    $solicitanteId = $row['solicitud_solicitante_id'];

    // Lógica de getSolicitudes.php: Encontrar al autorizador del solicitante
    // Buscamos quién tiene la 'id_autoridad' que coincide con el grupo del empleado
    $sqlBuscaJefe = "SELECT j.correo, j.nombre 
                     FROM empleados e
                     JOIN autoridad_departamental ad ON e.id_autoridad = ad.id
                     JOIN empleados j ON ad.clave_autorizador = j.id_autoridad
                     WHERE e.id = ? AND j.id_autoridad != '' LIMIT 1";
    
    /* NOTA: Esta consulta asume que el autorizador es quien encabeza 
       el grupo de autoridad asignado al empleado.
    */
    
    $stmt = $mysqli_vacaciones->prepare($sqlBuscaJefe);
    $stmt->bind_param("s", $solicitanteId);
    $stmt->execute();
    $resJefe = $stmt->get_result();

    if ($jefe = $resJefe->fetch_assoc()) {
        $correoJefe = $jefe['correo'];
        if (!empty($correoJefe)) {
            $pendientesPorAutorizador[$correoJefe]['nombre'] = $jefe['nombre'];
            $pendientesPorAutorizador[$correoJefe]['solicitudes'][] = $solicitudId;
        }
    }
    $stmt->close();
}

// 3. Enviar correos agrupados
$template_path = __DIR__ . '/../mail/solicitud_reclutar.html';
$url_intranet = "https://gpoalze.cloud/solicitud-personal/menu.html";

foreach ($pendientesPorAutorizador as $correo => $datos) {
    $count = count($datos['solicitudes']);
    $nombreJefe = $datos['nombre'];
    
    // Construir lista de IDs para el cuerpo del mensaje
    $lista_ids = "<ul>";
    foreach ($datos['solicitudes'] as $id) {
        $lista_ids .= "<li>Solicitud #<b>$id</b></li>";
    }
    $lista_ids .= "</ul>";

    $contenidoHTML = '
    <div style="width: 100%; margin-bottom: 20px; text-align: center; font-family: Arial, sans-serif;">
        <img src="https://gpoalze.cloud/solicitud-personal/assets/GRUPO_ALZE.png" alt="Logo" width="100">
    </div>
    <p>Estimado(a) <b>' . $nombreJefe . '</b>,</p>
    <p>Tienes <b>' . $count . '</b> solicitudes de personal pendientes de tu autorización (Nivel 1):</p>
    ' . $lista_ids . '
    <p>Por favor, ingresa al sistema para revisarlas y proceder con la autorización correspondiente.</p>';

    // Cargar Template
    $template = file_exists($template_path) 
        ? file_get_contents($template_path) 
        : "<html><body style='font-family: Arial, sans-serif;'>{{CONTENIDO}}<br><br><a href='{{URL_INTRANET}}' style='background-color: #006432; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Solicitudes en Intranet</a></body></html>";

    $correoHTML = str_replace(['{{CONTENIDO}}','{{URL_INTRANET}}'], [$contenidoHTML, $url_intranet], $template);
    $asunto = "URGENTE: $count Solicitudes Pendientes de su Autorización";

    // Enviar
    enviarCorreo($asunto, [$correo], $correoHTML, "SOLICITUDES NIVEL 1");
    echo "Recordatorio enviado a: $correo ($count pendientes).<br>";
}
?>