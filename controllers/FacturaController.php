<?php
require_once __DIR__ . "/../models/PedidoModel.php";
require_once __DIR__ . "/../models/PagoModel.php";

// Una misma factura HTML/PDF, con permisos distintos para cliente y personal.
class FacturaController {
    private $pedidoModel;
    private $pagoModel;
    private $paraPersonal;

    public function __construct($conexion, $paraPersonal = false) {
        requerirRol($paraPersonal ? ["empleado", "administrador"] : "cliente");
        $this->paraPersonal = $paraPersonal;
        $this->pedidoModel = new PedidoModel($conexion);
        $this->pagoModel = new PagoModel($conexion);
    }

    public function mostrar($descargar = false) {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            header("Allow: GET");
            $this->error(405, "Método no permitido.");
            return;
        }
        $id = $_GET["id"] ?? null;
        $id = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        $pedido = null;
        if ($id) {
            $pedido = $this->paraPersonal ? $this->pedidoModel->obtenerParaGestion($id)
                : $this->pedidoModel->obtenerFacturaDelCliente($id, usuarioActualId());
        }
        if (!$pedido) {
            $this->error(404, "Pedido no encontrado.");
            return;
        }
        if ($pedido["estado"] !== "pagado") {
            $this->error(409, "La factura estará disponible cuando el pago esté aprobado.");
            return;
        }
        $pago = $this->pagoModel->obtenerAprobado($id);
        $detalle = $this->pedidoModel->obtenerDetalle($id);
        $totalCentavos = 0;
        foreach ($detalle as &$linea) {
            $linea["precio_centavos"] = (int)round((float)$linea["precio_unitario"] * 100);
            $linea["subtotal_centavos"] = $linea["precio_centavos"] * (int)$linea["cantidad"];
            $totalCentavos += $linea["subtotal_centavos"];
        }
        unset($linea);
        // No se emite un documento sin pago aprobado o con un importe inconsistente.
        if (!$pago || empty($detalle) || (int)round((float)$pago["monto"] * 100) !== $totalCentavos) {
            $this->error(409, "No se pudo emitir la factura de este pedido. Comunicate con la tienda.");
            return;
        }
        $factura = ["numero" => str_pad((string)$id, 6, "0", STR_PAD_LEFT),
            "pedido" => $pedido, "pago" => $pago, "detalle" => $detalle, "total_centavos" => $totalCentavos];
        // El PDF se entrega en memoria. No se deja un archivo publico por pedido.
        header("Cache-Control: private, no-store, max-age=0");
        header("X-Content-Type-Options: nosniff");
        if ($descargar) {
            require_once __DIR__ . "/../views/pdf/FacturaPdf.php";
            $contenido = FacturaPdf::generar($factura);
            header("Content-Type: application/pdf");
            header('Content-Disposition: attachment; filename="factura-' . $factura["numero"] . '.pdf"');
            header("Content-Length: " . strlen($contenido));
            echo $contenido;
            return;
        }
        $rutaFactura = $this->paraPersonal ? "admin_pedido_factura" : "factura";
        $urlPdf = "index.php?pagina=" . $rutaFactura . "_pdf&id=" . $id;
        $urlVolver = "index.php?pagina=" . ($this->paraPersonal ? "admin_pedido" : "pedido") . "&id=" . $id;
        require __DIR__ . "/../views/factura.php";
    }

    private function error($codigo, $mensaje) {
        http_response_code($codigo);
        $mensajeError = $mensaje;
        require __DIR__ . "/../views/error.php";
    }
}
