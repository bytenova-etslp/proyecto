<?php
// Funciones de apoyo para mostrar datos y gestionar la sesion.
function escapar($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
}

function redirigir($pagina) {
    header("Location: index.php?pagina=" . $pagina);
    exit;
}

function estaLogueado() {
    return isset($_SESSION["id_usuario"]);
}

function usuarioActualId() {
    return $_SESSION["id_usuario"] ?? null;
}

function usuarioActualNombre() {
    return $_SESSION["nombre"] ?? null;
}

function usuarioActualRol() {
    return $_SESSION["rol"] ?? null;
}

function iniciarSesionUsuario($usuario) {
    session_regenerate_id(true);
    $_SESSION["id_usuario"] = (int)$usuario["id_usuario"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];
}

function cerrarSesion() {
    $_SESSION = [];
    session_destroy();
}
