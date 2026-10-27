<?php
require_once __DIR__ . "/../models/PedidoModel.php";
require_once __DIR__ . "/../models/PagoModel.php";

class PedidoController {
    private $modelo;
    private $pagoModel;

    public function __construct($conexion) {
        $this->modelo = new PedidoModel($conexion);
        $this->pagoModel = new PagoModel($conexion);
    }

    public function confirmar() {
        // El cliente autenticado sale de la sesion, nunca de los datos del navegador.
        if (!estaLogueado() || usuarioActualRol() !== "cliente") {
            http_response_code(403);
            echo json_encode(["correcto" => false, "mensaje" => "Se requiere una cuenta de cliente."]);
            return;
        }
        $json = $_POST["carrito"] ?? null;
        $items = is_string($json) ? json_decode($json, true) : null;
        if (!is_array($items) || !array_is_list($items) || count($items) === 0) {
            $this->responderError("El carrito esta vacio o sus datos no son validos.");
            return;
        }

        $carrito = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item["id"], $item["cantidad"], $item["talle"])
                || !is_int($item["id"]) || $item["id"] < 1 || $item["id"] > 2147483647
                || !is_int($item["cantidad"]) || $item["cantidad"] < 1 || $item["cantidad"] > 2147483647
                || !is_string($item["talle"]) || trim($item["talle"]) === ""
                || mb_strlen($item["talle"], "UTF-8") > 10) {
                $this->responderError("Revisa los productos, talles y cantidades del carrito.");
                return;
            }
            // Los precios y el stock recibidos se ignoran.
            $carrito[] = ["id" => $item["id"], "talle" => $item["talle"], "cantidad" => $item["cantidad"]];
        }

        try {
            $idPedido = $this->modelo->crearCompleto(usuarioActualId(), $carrito);
            echo json_encode(["correcto" => true, "id_pedido" => $idPedido]);
        } catch (InvalidArgumentException $error) {
            $this->responderError($error->getMessage());
        }
    }

    public function ver() {
        $pedido = $this->obtenerPedidoDelCliente();
        if (!$pedido) return;
        $detalle = $this->modelo->obtenerDetalle($pedido["id_pedido"]);
        $total = $this->calcularTotal($detalle);
        $pagos = $this->pagoModel->listarDelPedido($pedido["id_pedido"]);
        require __DIR__ . "/../views/pedido.php";
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
            redirigir("pago_resultado", "&id=" . (int)$pedido["id_pedido"]);
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
                    redirigir("pago_resultado", "&id=" . (int)$pedido["id_pedido"]);
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
