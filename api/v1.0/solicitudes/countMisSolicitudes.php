<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Solo necesitamos la conexión a la base de datos de solicitudes.
require "../conexion_solicitud.php"; 

// Obtener el ID del usuario
$idUser = $_POST['user-id'] ?? null;  
$idUser = "264";  

// Inicialización para la respuesta
$countPendientes = 0; 
$success = false;

if ($idUser) {
    // --- CONSTRUCCIÓN DE LA CONSULTA SQL ---
    // Condición Obligatoria del Usuario (Solicitante)
    // Y Condición de Pendiente: Autorización 1 es AUTORIZADA PERO Autorización 2 es NULL
    // NOTA: Se asume que una solicitud PENDIENTE es aquella que pasó la autorizador 1 
    // pero aún no tiene respuesta del autorizador 2 (NULL).
    // Si necesitas incluir solicitudes sin ninguna autorización (solicitud_autorizacion1 IS NULL), 
    // la cláusula WHERE debería ajustarse a:
    // (solicitud_autorizacion1 IS NULL OR (solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 IS NULL))

    $sqlCount = "SELECT COUNT(*) AS total_pendientes 
                 FROM sp_solicitud 
                 WHERE solicitud_solicitante_id = '" . $idUser . "' 
                 AND (solicitud_autorizacion1 IS NULL OR (solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 IS NULL))";

    // --- EJECUCIÓN DE LA CONSULTA ---
    $resultCount = $mysqli_solicitud->query($sqlCount);

    if ($resultCount) {
        $row = $resultCount->fetch_assoc();
        $countPendientes = (int)($row['total_pendientes'] ?? 0);
        $success = true;
    }
}

// --------------------------------------------------------------------------------------
## 📤 Salida JSON

// 1. Establecer el encabezado de respuesta para JSON
header('Content-Type: application/json');

// 2. Convertir el array a JSON y enviarlo al navegador
echo json_encode([
    'success' => $success,
     'id_usuario' => $idUser,
           'script' => "countMisSolicitudes",
    'total_solicitudes_pendientes' => $countPendientes
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 

exit; 
?>