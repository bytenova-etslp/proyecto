<?php
session_start();
require_once __DIR__ . "/config/auth.php";
// Punto de entrada de las paginas del sitio.
require_once __DIR__ . "/controllers/ProductoController.php";
require_once __DIR__ . "/controllers/UsuarioController.php";

$paginaSolicitada = $_GET["pagina"] ?? "inicio";

switch ($paginaSolicitada) {
    case "inicio":
    case "productos":
    case "producto":
        require_once __DIR__ . "/php/conexion.php";
        mysqli_set_charset($conexion, "utf8mb4");
        $controlador = new ProductoController($conexion);
        if ($paginaSolicitada === "inicio") {
            $controlador->mostrarInicio();
        } elseif ($paginaSolicitada === "productos") {
            $controlador->mostrarCatalogo();
        } else {
            $controlador->mostrarDetalle();
        }
        break;

    case "registro":
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            require __DIR__ . "/views/registro.php";
        } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
            header("Content-Type: application/json; charset=utf-8");
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                require_once __DIR__ . "/php/conexion.php";
                mysqli_set_charset($conexion, "utf8mb4");
                $controlador = new UsuarioController($conexion);
                $controlador->procesarRegistro();
            } catch (mysqli_sql_exception $error) {
                http_response_code(500);
                echo json_encode(["correcto" => false, "mensaje" => "No se pudo guardar la cuenta. Intenta nuevamente."]);
            }
        } else {
            header("Allow: GET, POST");
            http_response_code(405);
            $mensajeError = "Metodo no permitido.";
            require __DIR__ . "/views/error.php";
        }
        break;

    case "carrito":
        require __DIR__ . "/views/carrito.php";
        break;

    case "login":
    case "logout":
        $metodo = $_SERVER["REQUEST_METHOD"];
        if (($paginaSolicitada === "login" && !in_array($metodo, ["GET", "POST"], true))
            || ($paginaSolicitada === "logout" && $metodo !== "POST")) {
            header($paginaSolicitada === "logout" ? "Allow: POST" : "Allow: GET, POST");
            http_response_code(405);
            $mensajeError = "Metodo no permitido.";
            require __DIR__ . "/views/error.php";
            break;
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            require_once __DIR__ . "/php/conexion.php";
            mysqli_set_charset($conexion, "utf8mb4");
            $controlador = new UsuarioController($conexion);
            if ($paginaSolicitada === "login") {
                $controlador->login();
            } else {
                $controlador->logout();
            }
        } catch (mysqli_sql_exception $error) {
            http_response_code(500);
            $mensajeError = "No se pudo comprobar la cuenta. Intenta nuevamente.";
            require __DIR__ . "/views/error.php";
        }
        break;

    default:
        http_response_code(404);
        $mensajeError = "Pagina no encontrada.";
        require __DIR__ . "/views/error.php";
        break;
}
