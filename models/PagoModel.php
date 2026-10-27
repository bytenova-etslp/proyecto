<?php
class PagoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function registrarIntento($idPedido, $idUsuario, $ultimos4, $resultado) {
        if (!in_array($resultado, ["aprobado", "rechazado"], true)) {
            throw new InvalidArgumentException("Resultado de pago no valido.");
        }
        $estado = $resultado === "aprobado" ? "pagado" : "rechazado";
        $this->conexion->begin_transaction();
        try {
            // El UPDATE condicionado impide modificar un pedido que ya fue pagado.
            // Se realiza antes del INSERT y ambos cambios se confirman juntos.
            $consulta = $this->conexion->prepare("UPDATE Pedido SET estado = ?, metodo_pago = 'tarjeta_ficticia'
                WHERE id_pedido = ? AND id_usuario = ? AND estado IN ('pendiente', 'rechazado')");
            $consulta->bind_param("sii", $estado, $idPedido, $idUsuario);
            $consulta->execute();
            $modificados = $consulta->affected_rows;
            $consulta->close();

            if ($modificados === 0) {
                // MySQL tambien devuelve cero si otro intento rechazado deja
                // exactamente los mismos valores. En ese caso se permite registrarlo.
                $consulta = $this->conexion->prepare("SELECT estado FROM Pedido WHERE id_pedido = ? AND id_usuario = ?");
                $consulta->bind_param("ii", $idPedido, $idUsuario);
                $consulta->execute();
                $pedido = $consulta->get_result()->fetch_assoc();
                $consulta->close();
                if (!$pedido || $estado !== "rechazado" || $pedido["estado"] !== "rechazado") {
                    $this->conexion->rollback();
                    return false;
                }
            }

            // El importe se calcula desde el detalle guardado, nunca desde POST.
            $consulta = $this->conexion->prepare("INSERT INTO Pago (id_pedido, monto, resultado, ultimos4)
                SELECT ?, SUM(cantidad * precio_unitario), ?, ? FROM Detalle_Pedido
                WHERE id_pedido = ? HAVING COUNT(*) > 0");
            $consulta->bind_param("issi", $idPedido, $resultado, $ultimos4, $idPedido);
            $consulta->execute();
            $guardados = $consulta->affected_rows;
            $consulta->close();
            if ($guardados !== 1) {
                throw new mysqli_sql_exception("El pedido no tiene detalle para pagar.");
            }
            $this->conexion->commit();
            return true;
        } catch (Throwable $error) {
            $this->conexion->rollback();
            throw $error;
        }
    }

    // El controlador verifica primero que el pedido pertenezca al cliente.
    public function listarDelPedido($idPedido) {
        $consulta = $this->conexion->prepare("SELECT id_pago, monto, fecha, resultado, ultimos4 FROM Pago WHERE id_pedido = ? ORDER BY id_pago DESC");
        $consulta->bind_param("i", $idPedido);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $pagos = [];
        while ($pago = $resultado->fetch_assoc()) {
            $pagos[] = $pago;
        }
        $consulta->close();
        return $pagos;
    }
}
