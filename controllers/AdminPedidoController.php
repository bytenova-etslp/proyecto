<?php
require_once __DIR__ . "/../models/PedidoModel.php";

class AdminPedidoController {
    private $modelo;
    private $estados = ["pendiente", "rechazado", "pagado", "cancelado"];

    public function __construct($conexion) {
        requerirRol(["empleado", "administrador"]);
        $this->modelo = new PedidoModel($conexion);
    }

    public function listar() {
        $estadoSeleccionado = $_GET["estado"] ?? "";
        if (!is_string($estadoSeleccionado)
            || ($estadoSeleccionado !== "" && !in_array($estadoSeleccionado, $this->estados, true))) {
            http_response_code(400);
            $mensajeError = "Elegí un estado de pedido válido.";
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
        $tokenCancelacion = tokenCancelacionPedidos();
        $urlCancelar = "index.php?pagina=admin_pedido_cancelar&id=" . (int)$id;
        $avisoCancelacion = avisoCancelacionPedido();
        require __DIR__ . "/../views/admin/pedido_detalle.php";
    }

    public function cancelar() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !tokenCancelacionValido($_POST["token"] ?? null)) {
            $esPost = $_SERVER["REQUEST_METHOD"] === "POST";
            if (!$esPost) header("Allow: POST");
            http_response_code($esPost ? 403 : 405);
            $mensajeError = $esPost ? "El formulario no es válido. Volvé al detalle del pedido e intentá nuevamente." : "Método no permitido.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        if (!$id || !$this->modelo->obtenerParaGestion($id)) {
            http_response_code(404);
            $mensajeError = "Pedido no encontrado.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $cancelado = $this->modelo->cancelar($id);
        redirigir("admin_pedido", "&id=" . $id . "&cancelacion=" . ($cancelado ? "ok" : "no_disponible"));
    }
}
