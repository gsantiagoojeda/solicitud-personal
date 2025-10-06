<?php
require "../conexion_turnos.php";

$sqlTurnos = "select * from turnos";
$resultTurnos = $mysqli_turnos->query($sqlTurnos);
$array = [];
while ($rowTurnos = $resultTurnos->fetch_assoc())   $array[] = $rowTurnos;

echo json_encode($array);
