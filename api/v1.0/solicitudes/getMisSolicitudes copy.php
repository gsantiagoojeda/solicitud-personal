<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";

$idUser = $_POST['user-id'] ?? null; 

$filterAuth = $_POST['filterAuth'] ?? 'false'; 
$filterPend = $_POST['filterPend'] ?? 'false'; 
$filterRech = $_POST['filterRech'] ?? 'false'; 
$filterYearStart = $_POST['filterYearStart'] ?? null; 
$filterYearEnd = $_POST['filterYearEnd'] ?? null;


// $idUser ="264"; // usuario actual
// $filterAuth = "false"; 
// $filterPend = "false"; 
// $filterRech = "true"; 
// $filterYearStart = "2025"; 
// $filterYearEnd = "2025";


// --- PASO 1: Obtener datos del empleado actual ($idUser) y definir la variable $user ---
$user = []; // Inicializamos $user como un array vacío

$sqlUser = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, correo, empresa, id_departamento 
             FROM empleados 
             WHERE id= '$idUser' ";
$resultUser = $mysqli_vacaciones->query($sqlUser); 

if ($resultUser && $rowUser = $resultUser->fetch_assoc()) {
    $nombreCompleto = trim(
        ($rowUser['nombre'] ?? '') . ' ' .
        ($rowUser['apellido_paterno'] ?? '') . ' ' .
        ($rowUser['apellido_materno'] ?? '')
    );

    $user = [
        'id' => $rowUser['id'],
        'nombre_completo' => $nombreCompleto,
        'puesto' => $rowUser['puesto'] ?? null,
        'correo' => $rowUser['correo'] ?? '',
        'empresa' => $rowUser['empresa'] ?? '',
        'id_departamento' => $rowUser['id_departamento'] ?? null,
    ];
} else {
    $user = [
        'id' => $idUser, 
        'nombre_completo' => 'Empleado No Encontrado', 
        'puesto' => null, 
        'correo' => '', 
        'empresa' => '', 
        'id_departamento' => null,
    ];
}

// --------------------------------------------------------------------------------------


// Paso previo: cargar departamentos
$departamentos = [];
$resultDeptos = $mysqli_vacaciones->query("SELECT id_departamento, nombre FROM departamentos");
if ($resultDeptos) {
    while ($row = $resultDeptos->fetch_assoc()) {
        $departamentos[$row['id_departamento']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo: cargar puestos
$puestos = [];
$resultPuestos = $mysqli_intranet->query("SELECT id_archivo, nombre FROM puestos");
if ($resultPuestos) {
    while ($row = $resultPuestos->fetch_assoc()) {
        $puestos[$row['id_archivo']] = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Paso previo: cargar empleados con puesto y nombre completo para autorizador1 y 2
$empleados = [];
$sql = "SELECT id, puesto, nombre, apellido_paterno, apellido_materno FROM empleados";
$resultEmps = $mysqli_vacaciones->query($sql);
if ($resultEmps) {
    while ($row = $resultEmps->fetch_assoc()) {
        $nombreCompleto = trim(
            ($row['nombre'] ?? '') . ' ' .
            ($row['apellido_paterno'] ?? '') . ' ' .
            ($row['apellido_materno'] ?? '')
        );
        $empleados[$row['id']] = [
            'puesto' => $row['puesto'] ?? null,
            'nombre_completo' => $nombreCompleto
        ];
    }
}


// --- INICIO DE CONSTRUCCIÓN DINÁMICA DE LA CONSULTA ---
$filtros = [];
$statusConditions = [];

// 2. Definición de las condiciones SQL por cada estado (USANDO COMPARACIÓN ESTRICTA)
if ($filterAuth === "true") {
    // CONDICIÓN AUTORIZADA: Ambos campos deben ser 'AUTORIZADA'
    $statusConditions[] = "(solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 = 'AUTORIZADA')";
}

if ($filterPend === "true") {
    // CONDICIÓN PENDIENTE: No hay Autorización 1 O Autorización 1 está 'AUTORIZADA' PERO Autorización 2 es NULL
    $statusConditions[] = "((solicitud_autorizacion1 IS NULL) OR (solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 IS NULL))";
}

if ($filterRech === "true") {
    // CONDICIÓN RECHAZADA: Autorización 1 es 'RECHAZADA'
    $statusConditions[] = "(solicitud_autorizacion1 = 'RECHAZADA')";
}

// 3. Combinación de los filtros de estado
if (!empty($statusConditions)) {
    // Si al menos un filtro está activo, se construye el filtro OR normal.
    $filtros[] = "(" . implode(" OR ", $statusConditions) . ")";
} else {
    // Si NINGÚN filtro de estado está activo, agregamos una condición imposible para no traer resultados.
    $filtros[] = "(1 = 0)"; 
}


// 4. Filtros de Rango de Año (Fecha)
if (!empty($filterYearStart) && !empty($filterYearEnd)) {
    // Usamos YEAR() para extraer el año del TIMESTAMP y BETWEEN para el rango.
    $filtros[] = "CAST(YEAR(solicitud_date_create) AS UNSIGNED) BETWEEN " . (int)$filterYearStart . " AND " . (int)$filterYearEnd;
}


// 5. Condición Obligatoria del Usuario (Solicitante)
$filtros[] = "solicitud_solicitante_id = '" . $idUser . "'"; 

// 6. Construcción Final de la Consulta
$clausulaWhere = " WHERE " . implode(" AND ", $filtros);
$sqlSolicitudes = "SELECT * FROM sp_solicitud" . $clausulaWhere;


// echo "SQL Generada: " . $sqlSolicitudes . "<br />";


// --- EJECUCIÓN DE LA CONSULTA Y PROCESAMIENTO ---

$listaSolicitudes = []; // Inicialización crucial
$resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

if ($resultSolicitudes) {
    // echo "Número de filas encontradas: " . $resultSolicitudes->num_rows . "<br />";
    
    while ($row = $resultSolicitudes->fetch_assoc()) {
        $solicitudBlindada = array_map(function($v){
            return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
        }, $row);
        
        
            // Puesto del solicitante (usando el ID del puesto guardado en la solicitud)
            $nombrePuesto = '';
            $puestoId = $solicitudBlindada['solicitud_puesto_id'] ?? '';
            if ($puestoId && isset($puestos[$puestoId])) {
                $nombrePuesto = $puestos[$puestoId];
            }

            // Departamento del usuario solicitante (usando el ID del departamento cargado en $user)
            $nombreDepartamento = '';
            $deptoId = $user['id_departamento'] ?? ''; 
            if ($deptoId && isset($departamentos[$deptoId])) {
                $nombreDepartamento = $departamentos[$deptoId];
            }

            // Autorizador1: nombre completo y puesto
            $aut1NombreCompleto = '';
            $aut1Puesto = '';
            $aut1Id = $solicitudBlindada['solicitud_autorizador1_id'] ?? '';
            if ($aut1Id && isset($empleados[$aut1Id])) {
                $aut1NombreCompleto = $empleados[$aut1Id]['nombre_completo'];
                $aut1PuestoId = $empleados[$aut1Id]['puesto'];
                $aut1Puesto = $puestos[$aut1PuestoId] ?? $aut1PuestoId; 
            }
            
            // Autorizador2: nombre completo y puesto
            $aut2NombreCompleto = '';
            $aut2Puesto = '';
            $aut2Id = $solicitudBlindada['solicitud_autorizador2_id'] ?? '';
            if ($aut2Id && isset($empleados[$aut2Id])) {
                $aut2NombreCompleto = $empleados[$aut2Id]['nombre_completo'];
                $aut2PuestoId = $empleados[$aut2Id]['puesto'];
                $aut2Puesto = $puestos[$aut2PuestoId] ?? $aut2PuestoId; 
            }
            
            // Unir los datos de la solicitud con los datos del usuario solicitante
            $solicitudConUsuario = array_merge($solicitudBlindada, [
                "usuario_id" => $user['id'] ?? null,
                "usuario_nombre_completo" => $user['nombre_completo'] ?? '',
                "usuario_puesto" => $user['puesto'] ?? null,
                "usuario_correo" => $user['correo'] ?? '',
                "usuario_empresa" => $user['empresa'] ?? '',
                "usuario_id_departamento" => $user['id_departamento'] ?? null,
                "usuario_departamento_nombre" => $nombreDepartamento,
                "solicitud_nombre_puesto" => $nombrePuesto,
                "autorizador1_nombre_completo" => $aut1NombreCompleto,
                "autorizador1_puesto" => $aut1Puesto,
                "autorizador2_nombre_completo" => $aut2NombreCompleto,
                "autorizador2_puesto" => $aut2Puesto
            ]);

            $listaSolicitudes[] = $solicitudConUsuario;
    }
}

// --------------------------------------------------------------------------------------
## 📤 Salida JSON

// 1. Establecer el encabezado de respuesta para JSON
header('Content-Type: application/json');

// 2. Convertir el array a JSON y enviarlo al navegador
echo json_encode([
    'success' => true,
    'total_solicitudes' => count($listaSolicitudes), 
    'empleado_solicitante' => $user, 
    'solicitudes' => $listaSolicitudes
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 

exit; 
?>