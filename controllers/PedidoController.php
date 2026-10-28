<?php
require_once __DIR__ . "/../models/PedidoModel.php";
require_once __DIR__ . "/../models/PagoModel.php";

class PedidoController {
    private $modelo;
    private $pagoModel;
    private $departamentos = [
        "Artigas", "Canelones", "Cerro Largo", "Colonia", "Durazno", "Flores", "Florida",
        "Lavalleja", "Maldonado", "Montevideo", "Paysandú", "Río Negro", "Rivera", "Rocha",
        "Salto", "San José", "Soriano", "Tacuarembó", "Treinta y Tres"
    ];

    public function __construct($conexion) {
        $this->modelo = new PedidoModel($conexion);
        $this->pagoModel = new PagoModel($conexion);
    }

    public function checkout() {
        requerirRol("cliente");
        $carrito = $this->leerCarrito();
        if (empty($carrito)) {
            redirigir("carrito");
        }
        try {
            $resumen = $this->modelo->resumirCarrito($carrito);
        } catch (InvalidArgumentException $error) {
            http_response_code(422);
            $mensajeError = $error->getMessage();
            require __DIR__ . "/../views/error.php";
            return;
        }
        $carritoJson = json_encode($carrito);
        $departamentos = $this->departamentos;
        $nombreDestinatario = usuarioActualNombre();
        require __DIR__ . "/../views/checkout.php";
    }

    public function confirmar() {
        // El cliente autenticado sale de la sesion, nunca de los datos del navegador.
        if (!estaLogueado() || usuarioActualRol() !== "cliente") {
            http_response_code(403);
            echo json_encode(["correcto" => false, "mensaje" => "Se requiere una cuenta de cliente."]);
            return;
        }
        $carrito = $this->leerCarrito();
        if (empty($carrito)) {
            $this->responderError("El carrito está vacío o sus datos no son válidos.");
            return;
        }
        $envio = $this->leerEnvio();
        $errores = $this->validarEnvio($envio);
        if (!empty($errores)) {
            $this->responderError(implode(" ", $errores));
            return;
        }
        try {
            // La direccion, el telefono, el pedido, su detalle y el stock
            // se guardan juntos dentro de la transaccion del modelo.
            $idPedido = $this->modelo->crearCompleto(usuarioActualId(), $carrito, $envio);
            echo json_encode(["correcto" => true, "id_pedido" => $idPedido]);
        } catch (InvalidArgumentException $error) {
            $this->responderError($error->getMessage());
        }
    }

    private function leerCarrito() {
        $json = $_POST["carrito"] ?? null;
        $items = is_string($json) ? json_decode($json, true) : null;
        if (!is_array($items) || !array_is_list($items) || count($items) === 0) {
            return [];
        }

        $carrito = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item["id"], $item["cantidad"], $item["talle"])
                || !is_int($item["id"]) || $item["id"] < 1 || $item["id"] > 2147483647
                || !is_int($item["cantidad"]) || $item["cantidad"] < 1 || $item["cantidad"] > 2147483647
                || !is_string($item["talle"]) || trim($item["talle"]) === ""
                || mb_strlen($item["talle"], "UTF-8") > 10) {
                return [];
            }
            // Los precios y el stock recibidos se ignoran.
            $carrito[] = ["id" => $item["id"], "talle" => $item["talle"], "cantidad" => $item["cantidad"]];
        }

        return $carrito;
    }

    private function leerEnvio() {
        $envio = [];
        foreach (["nombre", "direccion", "ciudad", "departamento", "telefono"] as $campo) {
            $valor = $_POST["envio_" . $campo] ?? "";
            $envio[$campo] = is_string($valor) ? trim($valor) : "";
        }
        return $envio;
    }

    private function validarEnvio(&$envio) {
        $errores = [];
        if (!$this->largoEntre($envio["nombre"], 3, 60)) {
            $errores[] = "El nombre del destinatario debe tener entre 3 y 60 caracteres.";
        }
        if (!$this->largoEntre($envio["direccion"], 5, 100)) {
            $errores[] = "La dirección debe tener entre 5 y 100 caracteres.";
        }
        if (!$this->largoEntre($envio["ciudad"], 2, 50)) {
            $errores[] = "La ciudad debe tener entre 2 y 50 caracteres.";
        }
        if (!in_array($envio["departamento"], $this->departamentos, true)) {
            $errores[] = "Elegí un departamento de la lista.";
        }
        $envio["telefono"] = preg_replace('/\s/', '', $envio["telefono"]);
        if (!preg_match('/^[0-9]{9}$/', $envio["telefono"])) {
            $errores[] = "El teléfono debe tener 9 dígitos, con formato 099 999 999.";
        }
        return $errores;
    }

    private function largoEntre($texto, $minimo, $maximo) {
        $largo = mb_strlen($texto, "UTF-8");
        return $largo >= $minimo && $largo <= $maximo;
    }

    public function ver() {
        $pedido = $this->obtenerPedidoDelCliente();
        if (!$pedido) return;
        $detalle = $this->modelo->obtenerDetalle($pedido["id_pedido"]);
        $total = $this->calcularTotal($detalle);
        $pagos = $this->pagoModel->listarDelPedido($pedido["id_pedido"]);
        $tokenCancelacion = tokenCancelacionPedidos();
        $urlCancelar = "index.php?pagina=cancelar_pedido&id=" . (int)$pedido["id_pedido"];
        $avisoCancelacion = avisoCancelacionPedido();
        require __DIR__ . "/../views/pedido.php";
    }

    public function cancelar() {
        requerirRol("cliente");
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !tokenCancelacionValido($_POST["token"] ?? null)) {
            $esPost = $_SERVER["REQUEST_METHOD"] === "POST";
            if (!$esPost) header("Allow: POST");
            http_response_code($esPost ? 403 : 405);
            $mensajeError = $esPost ? "El formulario no es válido. Volvé al detalle del pedido e intentá nuevamente." : "Método no permitido.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $pedido = $this->obtenerPedidoDelCliente();
        if (!$pedido) return;
        $cancelado = $this->modelo->cancelar((int)$pedido["id_pedido"], usuarioActualId());
        redirigir("pedido", "&id=" . (int)$pedido["id_pedido"]
            . "&cancelacion=" . ($cancelado ? "ok" : "no_disponible"));
    }

    public function misPedidos() {
        requerirRol("cliente");
        $pedidos = $this->modelo->listarDelCliente(usuarioActualId());
        require __DIR__ . "/../views/mis_pedidos.php";
    }

    public function pagar() {
        $pedido = $this->obtenerPedidoDelCliente();
        if (!$pedido) return;
        if ($pedido["estado"] === "pagado") {
            redirigir("factura", "&id=" . (int)$pedido["id_pedido"]);
        }
        if (!in_array($pedido["estado"], ["pendiente", "rechazado"], true)) {
            http_response_code(409);
            $mensajeError = "El pedido no esta disponible para pagar.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $total = $this->calcularTotal($this->modelo->obtenerDetalle($pedido["id_pedido"]));
        $titular = "";
        $errores = [];
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titular = is_string($_POST["titular"] ?? null) ? trim($_POST["titular"]) : "";
            $numero = is_string($_POST["numero"] ?? null) ? trim($_POST["numero"]) : "";
            $vencimiento = is_string($_POST["vencimiento"] ?? null) ? trim($_POST["vencimiento"]) : "";
            $cvv = is_string($_POST["cvv"] ?? null) ? $_POST["cvv"] : "";
            $numero = str_replace([" ", "-"], "", $numero);
            $errores = $this->validarTarjeta($titular, $numero, $vencimiento, $cvv);
            if (empty($errores)) {
                $resultado = $this->simularPasarela($numero);
                $registrado = $this->pagoModel->registrarIntento(
                    (int)$pedido["id_pedido"], usuarioActualId(), substr($numero, -4), $resultado
                );
                if ($registrado) {
                    redirigir($resultado === "aprobado" ? "factura" : "pago_resultado", "&id=" . (int)$pedido["id_pedido"]);
                }
                // Otro envio pudo haber pagado el pedido mientras se completaba el formulario.
                redirigir("pedido", "&id=" . (int)$pedido["id_pedido"]);
            }
        }
        require __DIR__ . "/../views/pago.php";
    }

    public function resultado() {
        $pedido = $this->obtenerPedidoDelCliente();
        if (!$pedido) return;
        if ($pedido["estado"] === "pendiente") {
            redirigir("pago", "&id=" . (int)$pedido["id_pedido"]);
        }
        if (!in_array($pedido["estado"], ["pagado", "rechazado"], true)) {
            redirigir("pedido", "&id=" . (int)$pedido["id_pedido"]);
        }
        $total = $this->calcularTotal($this->modelo->obtenerDetalle($pedido["id_pedido"]));
        require __DIR__ . "/../views/pago_resultado.php";
    }

    private function obtenerPedidoDelCliente() {
        requerirRol("cliente");
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        $pedido = $id ? $this->modelo->obtenerDelCliente($id, usuarioActualId()) : null;
        if (!$pedido) {
            http_response_code(404);
            $mensajeError = "Pedido no encontrado.";
            require __DIR__ . "/../views/error.php";
            return null;
        }
        return $pedido;
    }

    private function calcularTotal($detalle) {
        $total = 0;
        foreach ($detalle as $linea) {
            $total += (int)$linea["cantidad"] * (float)$linea["precio_unitario"];
        }
        return $total;
    }

    private function validarTarjeta($titular, $numero, $vencimiento, $cvv) {
        $errores = [];
        $largoTitular = mb_strlen($titular, "UTF-8");
        if ($largoTitular < 3 || $largoTitular > 60) {
            $errores[] = "El nombre del titular debe tener entre 3 y 60 caracteres.";
        }
        if (!preg_match('/^[0-9]{16}$/', $numero)) {
            $errores[] = "El número de tarjeta debe tener 16 dígitos.";
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $vencimiento)) {
            $errores[] = "El vencimiento debe tener el formato MM/AA.";
        } else {
            $vence = DateTime::createFromFormat('!m/y', $vencimiento);
            $vence->modify('last day of this month')->setTime(23, 59, 59);
            if ($vence < new DateTime()) {
                $errores[] = "La tarjeta está vencida.";
            }
        }
        if (!preg_match('/^[0-9]{3}$/', $cvv)) {
            $errores[] = "El código de seguridad debe tener 3 dígitos.";
        }
        return $errores;
    }

    private function simularPasarela($numero) {
        // Regla de la guia docente: ultimo digito par aprueba; impar rechaza.
        return (int)substr($numero, -1) % 2 === 0 ? "aprobado" : "rechazado";
    }

    private function responderError($mensaje) {
        http_response_code(422);
        echo json_encode(["correcto" => false, "mensaje" => $mensaje]);
    }
}
