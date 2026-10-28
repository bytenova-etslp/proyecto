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

    public function obtenerAprobado($idPedido) {
        $consulta = $this->conexion->prepare("SELECT id_pago, monto, fecha, resultado, ultimos4
            FROM Pago WHERE id_pedido = ? AND resultado = 'aprobado' ORDER BY id_pago DESC LIMIT 1");
        $consulta->bind_param("i", $idPedido);
        $consulta->execute();
        $pago = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        return $pago;
    }

    // Se filtra por la fecha del cobro/intento, no por la creacion del pedido.
    // No se une con Detalle_Pedido: sus filas multiplicarian las transacciones.
    public function resumirPeriodo($inicio, $finExclusivo) {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) AS intentos,
            COALESCE(SUM(CASE WHEN resultado = 'aprobado' THEN 1 ELSE 0 END), 0) AS aprobados,
            COALESCE(SUM(CASE WHEN resultado = 'rechazado' THEN 1 ELSE 0 END), 0) AS rechazados,
            COALESCE(SUM(CASE WHEN resultado = 'aprobado' THEN monto ELSE 0 END), 0) AS total_cobrado
            FROM Pago WHERE fecha >= ? AND fecha < ?");
        $consulta->bind_param("ss", $inicio, $finExclusivo);
        $consulta->execute();
        $resumen = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        return $resumen;
    }

    public function listarPeriodo($inicio, $finExclusivo, $limite, $desplazamiento) {
        $consulta = $this->conexion->prepare("SELECT Pago.id_pago, Pago.id_pedido, Pago.fecha,
            Pago.resultado, Pago.monto, Pedido.estado AS estado_pedido,
            Usuario.nombre AS cliente_nombre, Usuario.email AS cliente_email
            FROM Pago JOIN Pedido ON Pedido.id_pedido = Pago.id_pedido
            JOIN Usuario ON Usuario.id_usuario = Pedido.id_usuario
            WHERE Pago.fecha >= ? AND Pago.fecha < ?
            ORDER BY Pago.fecha DESC, Pago.id_pago DESC LIMIT ? OFFSET ?");
        $consulta->bind_param("ssii", $inicio, $finExclusivo, $limite, $desplazamiento);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $pagos = [];
        while ($pago = $resultado->fetch_assoc()) $pagos[] = $pago;
        $consulta->close();
        return $pagos;
    }
}
