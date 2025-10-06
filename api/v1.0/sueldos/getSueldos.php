<?php
require "../conexion_solicitud.php";

$sqlSueldos = "select * from sp_sueldos";
$resultSueldos = $mysqli->query($sqlSueldos);
$array = [];
while ($rowSueldos = $resultSueldos->fetch_assoc())   $array[] = $rowSueldos;

echo json_encode($array);
