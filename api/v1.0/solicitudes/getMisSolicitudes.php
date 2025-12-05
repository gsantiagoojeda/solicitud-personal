<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";


// $idUser = $_POST['user-id']; // usuario actual
$idUser ="264"; // usuario actual

$filterAuth = "false"; 
$filterPend = "false"; 
$filterRech = "false"; 
$filterYearStart = "2025"; 
$filterYearEnd = "2025";
// $filterAuth = $_POST['filterAuth']; 
// $filterPend = $_POST['filterPend']; 
// $filterRech = $_POST['filterRech']; 
// $filterYearStart = $_POST['filterYearStart']; 
// $filterYearEnd = $_POST['filterYearEnd'];




// --- PASO 1: Obtener datos del empleado actual ($idUser) y definir la variable $user ---
$user = []; // Inicializamos $user como un array vacío

$sqlUser = "SELECT id, nombre, apellido_paterno, apellido_materno, puesto, correo, empresa, id_departamento 
             FROM empleados 
             WHERE id= '$idUser' ";
$resultUser = $mysqli_vacaciones->query($sqlUser); // Cambié la variable de $result a $resultUser para evitar conflictos

if ($resultUser && $rowUser = $resultUser->fetch_assoc()) {
    // Definimos $user con los datos del empleado
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
    // Si no se encuentra el empleado, inicializamos $user con valores seguros para evitar errores.
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


// 1. Array para almacenar las condiciones de los filtros
$filtros = [];
$statusConditions = [];

// 2. Definición de las condiciones SQL por cada estado

if ($filterAuth = "true") {
    // CONDICIÓN AUTORIZADA: Ambos campos deben ser 'AUTORIZADA'
    $statusConditions[] = "(solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 = 'AUTORIZADA')";
}

if ($filterPend = "true") {
    // CONDICIÓN PENDIENTE: No hay Autorización 1 O Autorización 1 está 'AUTORIZADA' PERO Autorización 2 es NULL
    $statusConditions[] = "((solicitud_autorizacion1 IS NULL) OR (solicitud_autorizacion1 = 'AUTORIZADA' AND solicitud_autorizacion2 IS NULL))";
}

if ($filterRech = "true") {
    // CONDICIÓN RECHAZADA: Autorización 1 es 'RECHAZADA'
    $statusConditions[] = "(solicitud_autorizacion1 = 'RECHAZADA')";
}

// 3. Combinación de los filtros de estado (si existen)
if (!empty($statusConditions)) {
    // Unimos todas las condiciones de estado con ' OR ' dentro de un gran paréntesis
    $filtros[] = "(" . implode(" OR ", $statusConditions) . ")";
}


// 4. Filtros de Rango de Año (Fecha) - Se mantiene la misma lógica
if (!empty($filterYearStart) && !empty($filterYearEnd)) {
    // Usamos YEAR() para extraer el año del TIMESTAMP y BETWEEN para el rango.
    $filtros[] = "CAST(YEAR(solicitud_date_create) AS UNSIGNED) BETWEEN " . (int)$filterYearStart . " AND " . (int)$filterYearEnd;
}


// 5. Condición Obligatoria del Usuario (Solicitante)
// ¡Recuerda sanitizar $userId o usar consultas preparadas!
$filtros[] = "solicitud_solicitante_id = '" . $idUser . "'";


// 6. Construcción Final de la Consulta
$clausulaWhere = " WHERE " . implode(" AND ", $filtros);

$sqlSolicitudes = "SELECT * FROM sp_solicitud" . $clausulaWhere;

echo "$sqlSolicitudes";

// Para depuración:
// echo $sqlSolicitudes;

// Para depuración:
// echo $sqlSolicitudes;
$listaSolicitudes = [];
    $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

if ($resultSolicitudes) {
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
                // Para obtener el nombre del puesto, se requiere otra búsqueda o asegurar que $empleados lo contenga.
                // Usaremos el ID de puesto y el array $puestos si 'puesto' en $empleados es un ID.
                $aut1PuestoId = $empleados[$aut1Id]['puesto'];
                $aut1Puesto = $puestos[$aut1PuestoId] ?? $aut1PuestoId; // Asume que 'puesto' en $empleados es el ID del puesto.
            }
            // Autorizador2: nombre completo y puesto
            $aut2NombreCompleto = '';
            $aut2Puesto = '';
            $aut2Id = $solicitudBlindada['solicitud_autorizador2_id'] ?? '';
            if ($aut2Id && isset($empleados[$aut2Id])) {
                $aut2NombreCompleto = $empleados[$aut2Id]['nombre_completo'];
                $aut2PuestoId = $empleados[$aut2Id]['puesto'];
                $aut2Puesto = $puestos[$aut2PuestoId] ?? $aut2PuestoId; // Asume que 'puesto' en $empleados es el ID del puesto.
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
    'empleado_solicitante' => $user, // Puedes incluir la info del empleado si es útil
    'solicitudes' => $listaSolicitudes
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // JSON_PRETTY_PRINT es opcional para formato legible

exit; // Detener la ejecución del script
?>