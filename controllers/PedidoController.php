<?php
require_once __DIR__ . "/../models/PedidoModel.php";

class PedidoController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new PedidoModel($conexion);
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
        requerirRol("cliente");
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        $pedido = $id ? $this->modelo->obtenerDelCliente($id, usuarioActualId()) : null;
        if (!$pedido) {
            http_response_code(404);
            $mensajeError = "Pedido no encontrado.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $detalle = $this->modelo->obtenerDetalle($id);
        $total = 0;
        foreach ($detalle as $linea) {
            $total += (int)$linea["cantidad"] * (float)$linea["precio_unitario"];
        }
        require __DIR__ . "/../views/pedido.php";
    }

    private function responderError($mensaje) {
        http_response_code(422);
        echo json_encode(["correcto" => false, "mensaje" => $mensaje]);
    }
}
