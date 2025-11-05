<?php
header("Content-Type: application/json; charset=utf-8");

// Mostrar errores para depurar
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../conexion_intranet.php"; // tu archivo con $mysqli_intranet

// Asegurar charset UTF-8
$mysqli_intranet->set_charset("utf8mb4");

// Verificar conexión
if ($mysqli_intranet->connect_error) {
    die(json_encode([
        "err" => true,
        "statusText" => "Error de conexión: " . $mysqli_intranet->connect_error
    ]));
}

if (!isset($_GET["solicitud_id"])) {
    die(json_encode([
        "err" => true,
        "statusText" => "Falta el parámetro solicitud_id"
    ]));
}

$solicitud_id = $_GET["solicitud_id"];

$query = "
SELECT 
    s.solicitud_id,
    s.solicitud_puesto_id,
    s.solicitud_espacio_trabajo,
    s.solicitud_espacio_trabajo_com,
    s.solicitud_mobiliario,
    s.solicitud_mobiliario_com,
    s.solicitud_equipo_computo,
    s.solicitud_equipo_computo_com,
    s.solicitud_herramientas,
    s.solicitud_herramientas_com,
    s.solicitud_compras_necesarias,
    s.solicitud_fecha_tentativa,
    s.solicitud_num_vacantes,
    s.solicitud_sexo,
    s.solicitud_estado_civil,
    s.solicitud_escolaridad,
    s.solicitud_edad_min,
    s.solicitud_edad_max,
    s.solicitud_experiencia,
    s.solicitud_conocimientos,
    s.solicitud_habilidades,
    s.solicitud_tools,
    s.solicitud_sueldo_id,
    s.solicitud_horario_id,
    s.solicitud_rolar,
    s.solicitud_solicitante_id,
    s.solicitud_autorizador1_id,
    s.solicitud_autorizacion1,
    s.solicitud_date_autorizacion1,
    s.solicitud_autorizador2_id,
    s.solicitud_autorizacion2,
    s.solicitud_date_autorizacion2,
    COALESCE(p.puesto_nombre, NULL) AS solicitud_puesto_nombre,
    COALESCE(CONCAT(su.sueldo_nombre, ':', su.sueldo_cantidad), NULL) AS solicitud_sueldo,
    COALESCE(CONCAT('Turno ', h.horario_id, ' :', h.horario_detalle), NULL) AS solicitud_horario,
    COALESCE(CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno), NULL) AS solicitud_solicitante,
    COALESCE(a1.nombre, NULL) AS solicitud_autorizador1
FROM sp_solicitudes s
LEFT JOIN sp_puestos p ON p.puesto_id = s.solicitud_puesto_id
LEFT JOIN sp_sueldos su ON su.sueldo_id = s.solicitud_sueldo_id
LEFT JOIN sp_horarios h ON h.horario_id = s.solicitud_horario_id
LEFT JOIN usuarios u ON u.usuario_id = s.solicitud_solicitante_id
LEFT JOIN usuarios a1 ON a1.usuario_id = s.solicitud_autorizador1_id
WHERE s.solicitud_id = ?
";

$stmt = $mysqli_intranet->prepare($query);
$stmt->bind_param("i", $solicitud_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "err" => true,
        "statusText" => "No se encontró la solicitud"
    ]);
    exit;
}

$solicitud = $result->fetch_assoc();

// ✅ Convertir cadenas vacías a null (sin borrar campos)
foreach ($solicitud as $key => $value) {
    if ($value === "") {
        $solicitud[$key] = null;
    }
}

// ✅ Devolver JSON limpio
echo json_encode([
    "solicitud" => $solicitud,
    "err" => false,
    "statusText" => "Consulta exitosa"
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$stmt->close();
$mysqli_intranet->close();
?>

