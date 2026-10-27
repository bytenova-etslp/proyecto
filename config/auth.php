<?php
// Funciones de apoyo para mostrar datos y gestionar la sesion.
function escapar($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
}

// Los telefonos nuevos se guardan como nueve digitos y se muestran en grupos de tres.
function formatearTelefono($telefono) {
    $telefono = (string)$telefono;
    if (preg_match('/^[0-9]{9}$/', $telefono)) {
        return substr($telefono, 0, 3) . " " . substr($telefono, 3, 3) . " " . substr($telefono, 6, 3);
    }
    return $telefono;
}

function redirigir($pagina, $extra = "") {
    header("Location: index.php?pagina=" . $pagina . $extra);
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

function esAdmin() {
    return usuarioActualRol() === "administrador";
}

function requerirLogin() {
    if (!estaLogueado()) {
        redirigir("login");
    }
}

function requerirRol($rol) {
    requerirLogin();
    $roles = is_array($rol) ? $rol : [$rol];
    if (!in_array(usuarioActualRol(), $roles, true)) {
        http_response_code(403);
        require __DIR__ . "/../views/sin_permiso.php";
        exit;
    }
}

// Revisa la cuenta actual y refresca nombre y rol antes de mostrar una pagina.
function sincronizarSesion($usuarioModel) {
    if (!estaLogueado()) {
        return;
    }
    $usuario = $usuarioModel->obtenerPorId(usuarioActualId());
    if (!$usuario || (int)$usuario["activo"] !== 1) {
        cerrarSesion();
        redirigir("login", "&cuenta=desactivada");
    }
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];
}

function puedeGestionarProductos() {
    return in_array(usuarioActualRol(), ["empleado", "administrador"], true);
}

function puedeConsultarPedidos() {
    return in_array(usuarioActualRol(), ["empleado", "administrador"], true);
}

function paginaInicialUsuario() {
    if (in_array(usuarioActualRol(), ["empleado", "administrador"], true)) {
        return "admin";
    }
    return "inicio";
}
