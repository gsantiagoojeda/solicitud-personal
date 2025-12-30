<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";


$idUser = "296"; // usuario actual

$filterAuth = $_POST['filterAuth'] ?? 'false'; 
$filterPend = $_POST['filterPend'] ?? 'false'; 
$filterRech = $_POST['filterRech'] ?? 'false'; 
$filterYearStart = $_POST['filterYearStart'] ?? null; 
$filterYearEnd = $_POST['filterYearEnd'] ?? null;

// Inicializar el contador total
$totalSolicitudes = 0; 


// Paso 1: Obtener puesto y autoridad del usuario (Necesario para la lógica de filtros)
$sqlUser = "SELECT puesto, id_autoridad FROM empleados WHERE id = ?";
$stmt = $mysqli_vacaciones->prepare($sqlUser);
if (!$stmt) {
    echo json_encode([
        "err" => true,
        "status" => "Error al preparar la consulta SQL"
    ]);
    exit;
}

$stmt->bind_param("s", $idUser);
$stmt->execute();
$stmt->bind_result($puesto, $autoridad);
if (!$stmt->fetch()) {
    echo json_encode([
        "err" => true,
        "status" => "Usuario no encontrado"
    ]);
    $stmt->close();
    $mysqli_vacaciones->close();
    exit;
}
$stmt->close();

// --------------------------------------------------------------------------------------
// --- CONSTRUCCIÓN DINÁMICA DE LOS FILTROS DE ESTADO Y AÑO (SE MANTIENE) ---
// --------------------------------------------------------------------------------------

$filtrosStatus = [];

// CONDICIÓN PENDIENTE
if ($filterPend === "true") {
    if ($puesto === 'Gerente de Recursos Humanos') {
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Autorizada' AND solicitud_autorizacion2 IS NULL)";
    } else {
      echo "entro a condicion pendiente";
        $filtrosStatus[] = "(solicitud_autorizacion1 IS NULL)";
    }
}

// CONDICIÓN RECHAZADA
if ($filterRech === "true") {
    if ($puesto === 'Gerente de Recursos Humanos') {
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Autorizada' AND solicitud_autorizacion2 = 'Rechazada')";
    } else {
        $filtrosStatus[] = "(solicitud_autorizacion1 = 'Rechazada')";
    }
}

$clausulaStatus = "";
if (!empty($filtrosStatus)) {
    // Si al menos un filtro está activo, se construye el filtro OR normal.
    $clausulaStatus = " AND (" . implode(" OR ", $filtrosStatus) . ")";
} else {
    // Si NINGÚN filtro está activo, no devolver nada.
    $clausulaStatus = " AND (1 = 0)"; 
}

// Filtros de Rango de Año (Fecha)
$clausulaYear = "";
if (!empty($filterYearStart) && !empty($filterYearEnd)) {
    $clausulaYear = " AND CAST(YEAR(solicitud_date_create) AS UNSIGNED) BETWEEN " . (int)$filterYearStart . " AND " . (int)$filterYearEnd;
}

// --------------------------------------------------------------------------------------
// --- FIN DE CONSTRUCCIÓN DINÁMICA DE LOS FILTROS ---
// --------------------------------------------------------------------------------------


// Paso 2: Conteo de solicitudes según el rol (RH vs. Director/Jefe)
if ($puesto === 'Gerente de Recursos Humanos') {
    // Lógica para RH: Busca solicitudes de Gerentes y Directores que ya pasaron la Aut1
    
    // 2.1 Obtener IDs de Gerentes y Directores (SOLO IDs)
    $sqlUsers = "SELECT id FROM empleados 
                 WHERE (puesto LIKE '%Gerente%' OR puesto LIKE '%Director%') AND status_empleado ='Activo' ";
    $result = $mysqli_vacaciones->query($sqlUsers);
    
    $listaUserAutorizadosIds = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listaUserAutorizadosIds[] = $mysqli_solicitud->real_escape_string($row['id']);
        }
    }
    
    if (!empty($listaUserAutorizadosIds)) {
        $userIds = "'" . implode("','", $listaUserAutorizadosIds) . "'";
        
        // 2.2 Consulta COUNT(*) única en sp_solicitud
        // Se mantiene la condición 'solicitud_autorizacion1 = Autorizada'
        $sqlCount = "SELECT COUNT(*) AS total 
                     FROM sp_solicitud 
                     WHERE solicitud_solicitante_id IN ($userIds) 
                       AND solicitud_autorizacion1 ='Autorizada'" 
                     . $clausulaStatus 
                     . $clausulaYear;
        
        $resultCount = $mysqli_solicitud->query($sqlCount);
        if ($resultCount) {
            $row = $resultCount->fetch_assoc();
            $totalSolicitudes = (int)($row['total'] ?? 0);
        }
    }
    
} else {
  echo "entro a obtener grupos";
    // Lógica para Director/Jefe: Busca solicitudes de su grupo de autoridad
    
    // 2.1 Obtener IDs de usuarios autorizados (SOLO IDs)
    $sqlAuth = "SELECT id FROM autoridad_departamental 
                WHERE clave_autorizador = ? OR id = ?";
    $stmtAuth = $mysqli_vacaciones->prepare($sqlAuth);
    $stmtAuth->bind_param("ss", $autoridad, $autoridad);
    $stmtAuth->execute();
    $stmtAuth->bind_result($authId);

    $listaGruposAutorizadosIds = [];
    while ($stmtAuth->fetch()) {
        $listaGruposAutorizadosIds[] = $mysqli_vacaciones->real_escape_string($authId);
    }
    $stmtAuth->close();
    
    $listaUserAutorizadosIds = [];
    if (!empty($listaGruposAutorizadosIds)) {
        $grupoClaves = "'" . implode("','", $listaGruposAutorizadosIds) . "'";
        $sqlUsers = "SELECT id FROM empleados 
                     WHERE id_autoridad IN ($grupoClaves) AND status_empleado ='Activo' ";
         echo $sqlUsers;           
        $result = $mysqli_vacaciones->query($sqlUsers);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $listaUserAutorizadosIds[] = $mysqli_solicitud->real_escape_string($row['id']);
            }
        }
    }
    
    if (!empty($listaUserAutorizadosIds)) {
        $userIds = "'" . implode("','", $listaUserAutorizadosIds) . "'";
        
        // 2.2 Consulta COUNT(*) única en sp_solicitud
        // Esta consulta aplica para Director/Jefe (solicitud_autorizacion1 IS NULL o 'Rechazada')
        $sqlCount = "SELECT COUNT(*) AS total 
                     FROM sp_solicitud 
                     WHERE solicitud_solicitante_id IN ($userIds)" 
                     . $clausulaStatus 
                     . $clausulaYear;

        $resultCount = $mysqli_solicitud->query($sqlCount);

        if ($resultCount) {
            $row = $resultCount->fetch_assoc();
            $totalSolicitudes = (int)($row['total'] ?? 0);
        }
    }
}


// Paso 3: Devolver JSON con el conteo
header('Content-Type: application/json');

if ($totalSolicitudes === 0) {
    echo json_encode([
        "err" => false,
        "statusText" => "No se encontraron solicitudes.",
          'id_usuario' => $idUser,
          'script' => "countSolicitudes",
        "data" => 0 // Devolver el conteo como 0
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "err" => false,
        "statusText" => "Conteo de solicitudes obtenido correctamente.",
          'id_usuario' => $idUser,
           'script' => "countSolicitudes",
        "data" => $totalSolicitudes // Devolver el conteo total
    ], JSON_UNESCAPED_UNICODE);
}
?>