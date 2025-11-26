<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require "../conexion_intranet.php";
require "../conexion_vacaciones.php";
require "../conexion_solicitud.php";


$idUser = $_POST['user-id']; // usuario actual



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

// Paso previo: cargar empleados con puesto y nombre completo para autorizador1
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


// Paso 4: Construir solicitudes
$listaSolicitudes = [];
$sqlSolicitudes;

   
    $userId = $mysqli_solicitud->real_escape_string($idUser);
    $sqlSolicitudes = "SELECT * FROM sp_solicitud WHERE solicitud_solicitante_id= '$userId' ";

    $resultSolicitudes = $mysqli_solicitud->query($sqlSolicitudes);

if ($resultSolicitudes) {
  while ($row = $resultSolicitudes->fetch_assoc()) {
    $solicitudBlindada = array_map(function($v){
      return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }, $row);
    
    
            // Puesto del solicitante
            $nombrePuesto = '';
            $puestoId = $solicitudBlindada['solicitud_puesto_id'] ?? '';
            if ($puestoId && isset($puestos[$puestoId])) {
                $nombrePuesto = $puestos[$puestoId];
            }

            // Departamento del usuario autorizado
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
                $aut1Puesto = $empleados[$aut1Id]['puesto'];
            }
            // Autorizador2: nombre completo y puesto
            $aut2NombreCompleto = '';
            $aut2Puesto = '';
            $aut2Id = $solicitudBlindada['solicitud_autorizador2_id'] ?? '';
            if ($aut2Id && isset($empleados[$aut2Id])) {
                $aut2NombreCompleto = $empleados[$aut2Id]['nombre_completo'];
                $aut2Puesto = $empleados[$aut2Id]['puesto'];
            }

            $solicitudConUsuario = array_merge($solicitudBlindada, [
                "usuario_id" => $user['id'],
                "usuario_nombre_completo" => $user['nombre_completo'],
                "usuario_puesto" => $user['puesto'],
                "usuario_correo" => $user['correo'] ?? '',
                "usuario_empresa" => $user['empresa'],
                "usuario_id_departamento" => $user['id_departamento'],
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
    
