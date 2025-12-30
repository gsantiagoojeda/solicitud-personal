<?php
require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";

$idUser = $_POST['user-id']; // usuario actual
$totalSolicitudes = 0; 

// Paso 1: Obtener puesto y autoridad del usuario
$sqlUser = "SELECT puesto, id_autoridad FROM empleados WHERE id = ?";
$stmt = $mysqli_vacaciones->prepare($sqlUser);
if (!$stmt) {
    echo json_encode(["err" => true, "status" => "Error al preparar la consulta SQL"]);
    exit;
}

$stmt->bind_param("s", $idUser);
$stmt->execute();
$stmt->bind_result($puesto, $autoridad);
if (!$stmt->fetch()) {
    echo json_encode(["err" => true, "status" => "Usuario no encontrado"]);
    $stmt->close();
    exit;
}
$stmt->close();

// --------------------------------------------------------------------------------------
// --- LÓGICA DE CONTEO SEGÚN ROL (SIN FILTROS EXTERNOS) ---
// --------------------------------------------------------------------------------------

if ($puesto === 'Gerente de Recursos Humanos') {
    // Caso RH: Ver solicitudes de Gerentes/Directores que ya tengan Autorización 1 pero NO la 2
    $sqlUsers = "SELECT id FROM empleados 
                 WHERE (puesto LIKE '%Gerente%' OR puesto LIKE '%Director%') AND status_empleado ='Activo'";
    $result = $mysqli_vacaciones->query($sqlUsers);
    
    $listaIds = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listaIds[] = $mysqli_solicitud->real_escape_string($row['id']);
        }
    }
    
    if (!empty($listaIds)) {
        $userIds = "'" . implode("','", $listaIds) . "'";
        // Solo contamos las que están pendientes de la firma de RH (Autorizacion 2)
        $sqlCount = "SELECT COUNT(*) AS total 
                     FROM sp_solicitud 
                     WHERE solicitud_solicitante_id IN ($userIds) 
                       AND solicitud_autorizacion1 = 'Autorizada' 
                       AND solicitud_autorizacion2 IS NULL";
        
        $res = $mysqli_solicitud->query($sqlCount);
        $totalSolicitudes = (int)($res->fetch_assoc()['total'] ?? 0);
    }
    
} else {
    // Caso Jefe/Director: Ver solicitudes de su personal que NO tengan Autorización 1
    $sqlAuth = "SELECT id FROM autoridad_departamental 
                WHERE clave_autorizador = ? OR id = ?";
    $stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
    $stmtAuth->bind_param("ss", $autoridad, $autoridad);
    $stmtAuth->execute();
    $stmtAuth->bind_result($authId);

    $gruposIds = [];
    while ($stmtAuth->fetch()) {
        $gruposIds[] = $mysqli_vacaciones->real_escape_string($authId);
    }
    $stmtAuth->close();
    
    if (!empty($gruposIds)) {
        $clanes = "'" . implode("','", $gruposIds) . "'";
        $sqlUsers = "SELECT id FROM empleados WHERE id_autoridad IN ($clanes) AND status_empleado ='Activo'";
        $result = $mysqli_vacaciones->query($sqlUsers);
        
        $listaIds = [];
        while ($row = $result->fetch_assoc()) {
            $listaIds[] = $mysqli_solicitud->real_escape_string($row['id']);
        }

        if (!empty($listaIds)) {
            $userIds = "'" . implode("','", $listaIds) . "'";
            // Solo contamos las que no han sido tocadas por el jefe (Autorizacion 1 es NULL)
            $sqlCount = "SELECT COUNT(*) AS total 
                         FROM sp_solicitud 
                         WHERE solicitud_solicitante_id IN ($userIds) 
                           AND solicitud_autorizacion1 IS NULL";

            $res = $mysqli_solicitud->query($sqlCount);
            $totalSolicitudes = (int)($res->fetch_assoc()['total'] ?? 0);
        }
    }
}

// Paso 3: Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "err" => false,
    "statusText" => "Conteo obtenido",
    "id_usuario" => $idUser,
    "data" => $totalSolicitudes 
], JSON_UNESCAPED_UNICODE);