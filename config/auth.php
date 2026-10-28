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

// Los formularios de cancelacion pertenecen a la sesion que los muestra.
function tokenCancelacionPedidos() {
    if (!isset($_SESSION["token_cancelacion_pedidos"])) {
        $_SESSION["token_cancelacion_pedidos"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["token_cancelacion_pedidos"];
}

function tokenCancelacionValido($valor) {
    $esperado = $_SESSION["token_cancelacion_pedidos"] ?? null;
    return is_string($valor) && is_string($esperado) && hash_equals($esperado, $valor);
}

function avisoCancelacionPedido() {
    $clave = $_GET["cancelacion"] ?? "";
    if ($clave === "ok") return "Pedido cancelado. Las unidades fueron devueltas al stock.";
    if ($clave === "no_disponible") return "El pedido ya no se puede cancelar. Revisá su estado actual.";
    return "";
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
