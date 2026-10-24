<?php
require_once __DIR__ . "/../models/UsuarioModel.php";

class UsuarioController {
    private $usuarioModel;

    public function __construct($conexion) {
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    public function procesarRegistro() {
        $nombre = $_POST["nombre"] ?? "";
        $email = $_POST["email"] ?? "";
        $contrasena = $_POST["contrasena"] ?? "";
        $confirmar = $_POST["confirmar"] ?? "";

        if (!is_string($nombre) || !is_string($email) || !is_string($contrasena) || !is_string($confirmar)) {
            $this->responder(false, "Los datos del formulario no son validos.", 400);
        }
        $nombre = trim($nombre);
        $email = trim($email);
        if ($nombre === "" || $email === "" || $contrasena === "" || $confirmar === "") {
            $this->responder(false, "Debe completar todos los campos.", 400);
        }
        if (preg_match('/^.{1,100}$/us', $nombre) !== 1) {
            $this->responder(false, "El nombre debe tener entre 1 y 100 caracteres.", 400);
        }
        if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responder(false, "Ingrese un correo valido de hasta 100 caracteres.", 400);
        }
        if (preg_match('/^.{8,}$/us', $contrasena) !== 1) {
            $this->responder(false, "La contrasena debe tener al menos 8 caracteres.", 400);
        }
        // Se conserva el limite de la version anterior para el hash de contrasenas.
        if (strlen($contrasena) > 72 || strpos($contrasena, "\0") !== false) {
            $this->responder(false, "La contrasena es demasiado larga o contiene caracteres no admitidos.", 400);
        }
        if ($contrasena !== $confirmar) {
            $this->responder(false, "Las contrasenas no coinciden.", 400);
        }

        try {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            if (!$this->usuarioModel->crear($nombre, $email, $hash)) {
                $this->responder(false, "No se pudo guardar la cuenta. Intenta nuevamente.", 500);
            }
            $this->responder(true, "Cuenta creada correctamente.", 201);
        } catch (mysqli_sql_exception $error) {
            // La restriccion UNIQUE de email sigue impidiendo cuentas repetidas.
            if ($error->getCode() == 1062) {
                $this->responder(false, "Ese correo ya esta registrado.", 409);
            }
            $this->responder(false, "No se pudo guardar la cuenta. Intenta nuevamente.", 500);
        }
    }

    public function login() {
        if (estaLogueado()) {
            redirigir("inicio");
        }
        $errores = [];
        $email = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $emailRecibido = $_POST["email"] ?? "";
            $contrasena = $_POST["contrasena"] ?? "";
            if (!is_string($emailRecibido) || !is_string($contrasena)) {
                $errores[] = "Los datos del formulario no son validos.";
            } else {
                $email = trim($emailRecibido);
                if ($email === "" || $contrasena === "") {
                    $errores[] = "Completa el correo y la contrasena.";
                } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "Ingresa un correo valido de hasta 100 caracteres.";
                } else {
                    $usuario = $this->usuarioModel->buscarPorEmail($email);
                    if ($usuario && password_verify($contrasena, $usuario["contrasena"])) {
                        if ((int)$usuario["activo"] !== 1) {
                            $errores[] = "Tu cuenta esta desactivada. Comunicate con el administrador.";
                        } else {
                            iniciarSesionUsuario($usuario);
                            redirigir("inicio");
                        }
                    } else {
                        $errores[] = "Correo o contrasena incorrectos.";
                    }
                }
            }
        }
        require __DIR__ . "/../views/login.php";
    }

    public function logout() {
        cerrarSesion();
        redirigir("login");
    }

    private function responder($correcto, $mensaje, $codigo = 200) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($codigo);
        echo json_encode(["correcto" => $correcto, "mensaje" => $mensaje]);
        exit;
    }
}
