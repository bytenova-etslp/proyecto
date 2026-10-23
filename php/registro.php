<?php
// Entrada del registro: carga la conexion y delega en el controlador.
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);
    echo json_encode(["correcto" => false, "mensaje" => "El registro requiere enviar el formulario."]);
    exit;
}

require_once __DIR__ . "/../controllers/UsuarioController.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    require_once __DIR__ . "/conexion.php";
    mysqli_set_charset($conexion, "utf8mb4");
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    echo json_encode(["correcto" => false, "mensaje" => "No se pudo guardar la cuenta. Intenta nuevamente."]);
    exit;
}

$controlador = new UsuarioController($conexion);
$controlador->procesarRegistro();
