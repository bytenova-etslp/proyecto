<?php
require_once __DIR__ . "/../models/PedidoModel.php";

class AdminPedidoController {
    private $modelo;
    private $estados = ["pendiente", "rechazado", "pagado"];

    public function __construct($conexion) {
        requerirRol(["empleado", "administrador"]);
        $this->modelo = new PedidoModel($conexion);
    }

    public function listar() {
        $estadoSeleccionado = $_GET["estado"] ?? "";
        if (!is_string($estadoSeleccionado)
            || ($estadoSeleccionado !== "" && !in_array($estadoSeleccionado, $this->estados, true))) {
            http_response_code(400);
            $mensajeError = "Elegí un estado de pago válido.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $estados = $this->estados;
        $pedidos = $this->modelo->listarParaGestion($estadoSeleccionado);
        require __DIR__ . "/../views/admin/pedidos_lista.php";
    }

    public function detalle() {
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        $pedido = $id ? $this->modelo->obtenerParaGestion($id) : null;
        if (!$pedido) {
            http_response_code(404);
            $mensajeError = "Pedido no encontrado.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $detalle = $this->modelo->obtenerDetalle($id);
        require __DIR__ . "/../views/admin/pedido_detalle.php";
    }
}
