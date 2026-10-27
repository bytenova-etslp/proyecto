<?php
require_once __DIR__ . "/../models/UsuarioModel.php";

class AdminUsuarioController {
    private $modelo;
    private $roles = ["cliente", "empleado", "administrador"];
    private $avisos = [
        "rol_cambiado" => ["tipo" => "exito", "texto" => "El rol del usuario fue actualizado."],
        "desactivado" => ["tipo" => "exito", "texto" => "La cuenta fue desactivada. Sus pedidos se conservan."],
        "reactivado" => ["tipo" => "exito", "texto" => "La cuenta fue reactivada."],
        "propio" => ["tipo" => "error", "texto" => "No podés cambiar tu propio rol ni el estado de tu propia cuenta."],
        "invalido" => ["tipo" => "error", "texto" => "Los datos no son válidos o el usuario ya no existe."],
        "formulario" => ["tipo" => "error", "texto" => "El formulario ya no es válido. Volvé a realizar el cambio desde esta página."],
        "fallo" => ["tipo" => "error", "texto" => "No se pudo guardar el cambio. Intentá nuevamente."]
    ];

    public function __construct($conexion) {
        requerirRol("administrador");
        $this->modelo = new UsuarioModel($conexion);
    }

    public function listar() {
        if (!$this->exigirMetodo("GET")) return;
        $usuarios = $this->modelo->listarTodosAdmin();
        $roles = $this->roles;
        $idActual = (int)usuarioActualId();
        $clave = $_GET["msg"] ?? "";
        $aviso = is_string($clave) ? ($this->avisos[$clave] ?? null) : null;
        // Vincula los formularios a la sesion que muestra el panel.
        if (!isset($_SESSION["token_usuarios"])) {
            $_SESSION["token_usuarios"] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION["token_usuarios"];
        require __DIR__ . "/../views/admin/usuarios_lista.php";
    }

    public function cambiarRol() {
        if (!$this->exigirMetodo("POST")) return;
        $this->validarFormulario();
        $id = $this->leerId();
        $rol = $_POST["rol"] ?? null;
        if ($id === null || !is_string($rol) || !in_array($rol, $this->roles, true)) {
            redirigir("admin_usuarios", "&msg=invalido");
        }
        $this->validarDestino($id);
        $correcto = $this->modelo->cambiarRol($id, $rol);
        redirigir("admin_usuarios", $correcto ? "&msg=rol_cambiado" : "&msg=fallo");
    }

    public function cambiarEstado() {
        if (!$this->exigirMetodo("POST")) return;
        $this->validarFormulario();
        $id = $this->leerId();
        $activo = $_POST["activo"] ?? null;
        if ($id === null || !is_string($activo) || !in_array($activo, ["0", "1"], true)) {
            redirigir("admin_usuarios", "&msg=invalido");
        }
        $this->validarDestino($id);
        // Se envia el estado deseado: repetir un POST no vuelve a alternarlo.
        $correcto = $this->modelo->cambiarActivo($id, (int)$activo);
        $mensaje = $correcto ? ($activo === "1" ? "reactivado" : "desactivado") : "fallo";
        redirigir("admin_usuarios", "&msg=" . $mensaje);
    }

    private function leerId() {
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        return $id === false ? null : $id;
    }

    private function validarDestino($id) {
        if ($id === (int)usuarioActualId()) {
            redirigir("admin_usuarios", "&msg=propio");
        }
        if (!$this->modelo->obtenerPorId($id)) {
            redirigir("admin_usuarios", "&msg=invalido");
        }
    }

    private function validarFormulario() {
        $token = $_POST["token"] ?? null;
        $esperado = $_SESSION["token_usuarios"] ?? null;
        if (!is_string($token) || !is_string($esperado) || !hash_equals($esperado, $token)) {
            redirigir("admin_usuarios", "&msg=formulario");
        }
    }

    private function exigirMetodo($metodo) {
        if ($_SERVER["REQUEST_METHOD"] === $metodo) return true;
        header("Allow: " . $metodo);
        http_response_code(405);
        $mensajeError = "Método no permitido.";
        require __DIR__ . "/../views/error.php";
        return false;
    }
}
