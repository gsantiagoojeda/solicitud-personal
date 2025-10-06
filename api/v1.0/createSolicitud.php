<?php

  require "./conexion_solicitud.php";

  //header("Access-Control-Allow-Origin: *");
  header('Content-Type: application/json');

$puesto = $_POST['puesto'];
$rp1 = $_POST['rp1'];
$rp2 = $_POST['rp2'];
$rp3 = $_POST['rp3'];
$rp4 = $_POST['rp4'];
$trp1 = $_POST['trp1'];
$trp2 = $_POST['trp2'];
$trp3 = $_POST['trp3'];
$trp4 = $_POST['trp4'];
$compras = $_POST['compras'];
$fechaTentativa = $_POST['fechaTentativa'];
$numVacantes = $_POST['numVacantes'];
$sexo = $_POST['sexo'];
$estadoCivil = $_POST['estadoCivil'];
$escolaridad = $_POST['escolaridad'];
$edadMinima = $_POST['edadMinima'];
$edadMaxima = $_POST['edadMaxima'];
$experiencia = $_POST['experiencia'];
$conocimientos = $_POST['conocimientos'];
$habilidades = $_POST['habilidades'];
$tools = $_POST['tools'];
$sueldo = $_POST['sueldo'];
$horario = $_POST['horario'];
$rolar = $_POST['rolar'];
$solicitante = $_POST['solicitante'];
$solicitantePuesto = $_POST['solicitante-puesto'];

$autorizador1=null;
$fechaAuth=null;
if(stripos($solicitantePuesto, "director") !== false){
  $autorizador1=$solicitante;
  $fechaAuth=date("Y-m-d H:i:s");
}


$insert = mysqli_query($mysql_solicitud, "
    INSERT INTO ti_solicitud_personal (
        solicitud_puesto_id,
        solicitud_espacio_trabajo,
        solicitud_espacio_trabajo_com,
        solicitud_mobiliario,
        solicitud_mobiliario_com,
        solicitud_equipo_computo,
        solicitud_equipo_computo_com,
        solicitud_herramientas,
        solicitud_herramientas_com,
        solicitud_compras_necesarias,
        solicitud_fecha_tentativa,
        solicitud_num_vacantes,
        solicitud_sexo,
        solicitud_estado_civil,
        solicitud_escolaridad,
        solicitud_edad_min,
        solicitud_edad_max,
        solicitud_experiencia,
        solicitud_conocimientos,
        solicitud_habilidades,
        solicitud_tools,
        solicitud_sueldo_id,
        solicitud_horario_id,
        solicitud_rolar,
        solicitud_solicitante_id,
        solicitud_autorizador1_id,
        solicitud_autorizacion1,
        solicitud_date_autorizacion1
    ) VALUES (
        '$puesto',
        '$rp1',
        '$rp2',
        '$rp3',
        '$rp4',
        '$trp1',
        '$trp2',
        '$trp3',
        '$trp4',
        '$compras',
        '$fechaTentativa',
        '$numVacantes',
        '$sexo',
        '$estadoCivil',
        '$escolaridad',
        '$edadMinima',
        '$edadMaxima',
        '$experiencia',
        '$conocimientos',
        '$habilidades',
        '$tools',
        '$sueldo',
        '$horario',
        '$rolar',
        '$solicitante',
        '$autorizador1',
        'Autorizada',
        '$fechaAuth'
    )
");

if ($insert) {
  $res = array(
    "err" => false,
    "statusText" => "Datos guardados correctamente"
  );

  echo json_encode($res);
  exit;
} else {
  $res = array(
    "err" => true,
    "statusText" => "Error al guardar datos"
  );

  echo json_encode($res);
  exit;
}
