<?php
session_start();
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

$key = "Zeal_2025$$";

if (!isset($_SESSION["jwt"])) {
    echo json_encode([
        "success" => false,
        "message" => "No hay token en la sesión"
    ]);
    exit;
}

$jwt = $_SESSION["jwt"];

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

    echo json_encode([
        "success" => true,
        "id" => $decoded->id, // 👈 este es el usuario logueado
        "datos"=>$decoded->data
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Token inválido o expirado"
    ]);
}
