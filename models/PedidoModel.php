<?php
class PedidoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearCompleto($idUsuario, $carrito, $envio) {
        $this->conexion->begin_transaction();
        try {
            $consulta = $this->conexion->prepare("INSERT INTO Pedido (fecha, estado, metodo_pago, id_usuario,
                envio_nombre, envio_direccion, envio_ciudad, envio_departamento, envio_telefono)
                VALUES (CURRENT_TIMESTAMP, 'pendiente', 'por_definir', ?, ?, ?, ?, ?, ?)");
            $consulta->bind_param("isssss", $idUsuario, $envio["nombre"], $envio["direccion"],
                $envio["ciudad"], $envio["departamento"], $envio["telefono"]);
            $consulta->execute();
            $idPedido = (int)$this->conexion->insert_id;
            $consulta->close();

            $variantes = [];
            foreach ($carrito as $item) {
                $variante = $this->obtenerVarianteParaCompra($item["id"], $item["talle"]);
                if (!$variante) {
                    throw new InvalidArgumentException("Un producto ya no esta activo o el talle no existe. Revisa el carrito.");
                }
                // Usar el talle de la base evita duplicados por diferencias de mayusculas.
                $clave = $item["id"] . ":" . $variante["talle"];
                if (isset($variantes[$clave])) {
                    throw new InvalidArgumentException("Hay un producto y talle repetidos. Vuelve a agregarlos desde el catalogo.");
                }
                $variantes[$clave] = true;
                if ((int)$variante["stock"] < $item["cantidad"]) {
                    throw new InvalidArgumentException("No hay stock suficiente para uno de los talles. Revisa las cantidades del carrito.");
                }
                $precio = $variante["precio_promocional"] !== null ? $variante["precio_promocional"] : $variante["precio"];
                $this->descontarStock($item["id"], $variante["talle"], $item["cantidad"]);
                $this->agregarDetalle($idPedido, $item["id"], $variante["talle"], $item["cantidad"], $precio);
            }
            $this->conexion->commit();
            return $idPedido;
        } catch (Throwable $error) {
            $this->conexion->rollback();
            throw $error;
        }
    }

    // Prepara el resumen sin registrar pedidos ni reservar existencias.
    public function resumirCarrito($items) {
        $lineas = [];
        $total = 0;
        $variantes = [];
        foreach ($items as $item) {
            $variante = $this->obtenerVarianteParaCompra($item["id"], $item["talle"]);
            if (!$variante) {
                throw new InvalidArgumentException("Un producto ya no está disponible o el talle no existe. Revisá el carrito.");
            }
            $clave = $item["id"] . ":" . $variante["talle"];
            if (isset($variantes[$clave])) {
                throw new InvalidArgumentException("Hay un producto y talle repetidos. Revisá el carrito.");
            }
            $variantes[$clave] = true;
            if ((int)$variante["stock"] < $item["cantidad"]) {
                throw new InvalidArgumentException("No hay stock suficiente para uno de los talles. Revisá las cantidades del carrito.");
            }
            $precio = $variante["precio_promocional"] ?? $variante["precio"];
            $subtotal = (float)$precio * $item["cantidad"];
            $lineas[] = ["id" => $item["id"], "nombre" => $variante["nombre"], "talle" => $variante["talle"],
                "cantidad" => $item["cantidad"], "precio" => $precio, "subtotal" => $subtotal];
            $total += $subtotal;
        }
        return ["lineas" => $lineas, "total" => $total];
    }

    private function obtenerVarianteParaCompra($idProducto, $talle) {
        $consulta = $this->conexion->prepare("SELECT Producto.nombre, Producto.precio, Producto_Talle.talle, Producto_Talle.stock,
            (SELECT MIN(Promocion_Producto.precio_promocional)
             FROM Promocion_Producto
             JOIN Promocion ON Promocion.id_promocion = Promocion_Producto.id_promocion
             WHERE Promocion_Producto.id_producto = Producto.id_producto
                 AND Promocion.activo = 1
                 AND Promocion_Producto.precio_promocional < Producto.precio
                 AND CURRENT_DATE BETWEEN Promocion.fecha_inicio AND Promocion.fecha_fin) AS precio_promocional
            FROM Producto JOIN Producto_Talle ON Producto_Talle.id_producto = Producto.id_producto
            WHERE Producto.id_producto = ? AND Producto.activo = 1 AND Producto_Talle.talle = ?");
        $consulta->bind_param("is", $idProducto, $talle);
        $consulta->execute();
        $variante = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        return $variante;
    }

    private function descontarStock($idProducto, $talle, $cantidad) {
        // Como en la guia de ABM: una sola resta condicionada evita stock negativo.
        $consulta = $this->conexion->prepare("UPDATE Producto_Talle SET stock = stock - ? WHERE id_producto = ? AND talle = ? AND stock >= ?");
        $consulta->bind_param("iisi", $cantidad, $idProducto, $talle, $cantidad);
        $consulta->execute();
        $actualizados = $consulta->affected_rows;
        $consulta->close();
        if ($actualizados !== 1) {
            throw new InvalidArgumentException("Las existencias cambiaron. Revisa el carrito antes de confirmar.");
        }
    }

    private function agregarDetalle($idPedido, $idProducto, $talle, $cantidad, $precio) {
        $consulta = $this->conexion->prepare("INSERT INTO Detalle_Pedido (id_pedido, id_producto, talle, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
        $consulta->bind_param("iisid", $idPedido, $idProducto, $talle, $cantidad, $precio);
        $consulta->execute();
        $consulta->close();
    }

    public function obtenerDelCliente($idPedido, $idUsuario) {
        $consulta = $this->conexion->prepare("SELECT id_pedido, fecha, estado, metodo_pago,
            envio_nombre, envio_direccion, envio_ciudad, envio_departamento, envio_telefono
            FROM Pedido WHERE id_pedido = ? AND id_usuario = ?");
        $consulta->bind_param("ii", $idPedido, $idUsuario);
        $consulta->execute();
        $pedido = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        return $pedido;
    }

    public function listarDelCliente($idUsuario) {
        $consulta = $this->conexion->prepare("SELECT Pedido.id_pedido, Pedido.fecha, Pedido.estado,
            COALESCE((SELECT SUM(cantidad * precio_unitario) FROM Detalle_Pedido
                WHERE Detalle_Pedido.id_pedido = Pedido.id_pedido), 0) AS total
            FROM Pedido WHERE id_usuario = ? ORDER BY Pedido.id_pedido DESC");
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $pedidos = [];
        while ($pedido = $resultado->fetch_assoc()) {
            $pedidos[] = $pedido;
        }
        $consulta->close();
        return $pedidos;
    }

    public function listarParaGestion($estado = "") {
        $sql = $this->sqlParaGestion();
        if ($estado !== "") {
            $sql .= " WHERE Pedido.estado = ?";
        }
        $sql .= " ORDER BY Pedido.id_pedido DESC";
        $consulta = $this->conexion->prepare($sql);
        if ($estado !== "") {
            $consulta->bind_param("s", $estado);
        }
        $consulta->execute();
        $resultado = $consulta->get_result();
        $pedidos = [];
        while ($pedido = $resultado->fetch_assoc()) {
            $pedidos[] = $pedido;
        }
        $consulta->close();
        return $pedidos;
    }

    // Solo se usa desde el controlador protegido del personal.
    public function obtenerParaGestion($idPedido) {
        $consulta = $this->conexion->prepare($this->sqlParaGestion() . " WHERE Pedido.id_pedido = ?");
        $consulta->bind_param("i", $idPedido);
        $consulta->execute();
        $pedido = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        return $pedido;
    }

    private function sqlParaGestion() {
        return "SELECT Pedido.id_pedido, Pedido.fecha, Pedido.estado, Pedido.metodo_pago,
            Pedido.envio_nombre, Pedido.envio_direccion, Pedido.envio_ciudad,
            Pedido.envio_departamento, Pedido.envio_telefono,
            Usuario.nombre AS cliente_nombre, Usuario.email AS cliente_email,
            COALESCE((SELECT SUM(cantidad * precio_unitario) FROM Detalle_Pedido
                WHERE Detalle_Pedido.id_pedido = Pedido.id_pedido), 0) AS total
            FROM Pedido JOIN Usuario ON Usuario.id_usuario = Pedido.id_usuario";
    }

    // El controlador verifica la propiedad del cliente o los permisos del personal.
    public function obtenerDetalle($idPedido) {
        $consulta = $this->conexion->prepare("SELECT Producto.nombre, Detalle_Pedido.talle, Detalle_Pedido.cantidad, Detalle_Pedido.precio_unitario
            FROM Detalle_Pedido JOIN Producto ON Producto.id_producto = Detalle_Pedido.id_producto
            WHERE Detalle_Pedido.id_pedido = ? ORDER BY Detalle_Pedido.id_producto, Detalle_Pedido.talle");
        $consulta->bind_param("i", $idPedido);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $detalle = [];
        while ($linea = $resultado->fetch_assoc()) {
            $detalle[] = $linea;
        }
        $consulta->close();
        return $detalle;
    }
}
