<?php
// Consultas de productos y existencias por talle. Devuelve arreglos, sin HTML.
class ProductoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function listarProductos($idCategoria = null, $busqueda = "") {
        $sql = "SELECT Producto.id_producto, Producto.nombre, Producto.precio, Producto.stock_minimo,
		(SELECT MIN(Promocion_Producto.precio_promocional)
            FROM Promocion_Producto
            JOIN Promocion ON Promocion_Producto.id_promocion = Promocion.id_promocion
            WHERE Promocion_Producto.id_producto = Producto.id_producto
                AND Promocion.activo = 1
                AND Promocion_Producto.precio_promocional < Producto.precio
                AND CURRENT_DATE BETWEEN Promocion.fecha_inicio AND Promocion.fecha_fin)
            AS precio_promocional,
		Producto.imagen, COALESCE((SELECT SUM(Producto_Talle.stock)
            FROM Producto_Talle
            WHERE Producto_Talle.id_producto = Producto.id_producto), 0) AS stock, Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		WHERE Producto.activo = 1";
        $tipos = "";
        $parametros = [];
        if ($idCategoria !== null) {
            $sql .= " AND Producto.id_categoria = ?";
            $tipos .= "i";
            $parametros[] = $idCategoria;
        }
        if ($busqueda !== "") {
            $sql .= " AND (Producto.nombre LIKE ? OR Producto.descripcion LIKE ?)";
            $tipos .= "ss";
            $patron = "%" . $this->escaparLike($busqueda) . "%";
            $parametros[] = $patron;
            $parametros[] = $patron;
        }
        $sql .= " ORDER BY Producto.id_producto ASC";
        $consulta = $this->conexion->prepare($sql);
        if (!empty($parametros)) {
            $consulta->bind_param($tipos, ...$parametros);
        }
        $consulta->execute();
        $resultado = $consulta->get_result();
        $productos = [];
        while ($producto = $resultado->fetch_assoc()) {
            $productos[] = $producto;
        }
        $consulta->close();
        return $productos;
    }

    public function obtenerProductoPorId($idProducto) {
        $sql = "SELECT Producto.id_producto, Producto.nombre, Producto.descripcion, Producto.precio,
		(SELECT MIN(Promocion_Producto.precio_promocional)
            FROM Promocion_Producto
            JOIN Promocion ON Promocion_Producto.id_promocion = Promocion.id_promocion
            WHERE Promocion_Producto.id_producto = Producto.id_producto
                AND Promocion.activo = 1
                AND Promocion_Producto.precio_promocional < Producto.precio
                AND CURRENT_DATE BETWEEN Promocion.fecha_inicio AND Promocion.fecha_fin)
            AS precio_promocional, Producto.imagen,
        COALESCE((SELECT SUM(Producto_Talle.stock)
            FROM Producto_Talle
            WHERE Producto_Talle.id_producto = Producto.id_producto), 0) AS stock,
        Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		WHERE Producto.id_producto = ? AND Producto.activo = 1";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idProducto);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $producto = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($consulta);
        return $producto;
    }

    public function listarTalles($idProducto) {
        $consulta = $this->conexion->prepare("SELECT talle, stock FROM Producto_Talle WHERE id_producto = ? ORDER BY stock DESC, talle ASC");
        $consulta->bind_param("i", $idProducto);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $talles = [];
        while ($talle = $resultado->fetch_assoc()) {
            $talles[] = $talle;
        }
        $consulta->close();
        return $talles;
    }

    public function listarCategorias() {
        $consulta = $this->conexion->prepare("SELECT id_categoria, nombre FROM Categoria ORDER BY nombre");
        $consulta->execute();
        $resultado = $consulta->get_result();
        $categorias = [];
        while ($categoria = $resultado->fetch_assoc()) {
            $categorias[] = $categoria;
        }
        $consulta->close();
        return $categorias;
    }

    public function listarTodosAdmin() {
        $sql = "SELECT Producto.id_producto, Producto.nombre, Producto.precio,
                       Producto.stock_minimo, Producto.imagen, Producto.activo,
                       Categoria.nombre AS categoria,
                       COALESCE((SELECT SUM(Producto_Talle.stock) FROM Producto_Talle
                           WHERE Producto_Talle.id_producto = Producto.id_producto), 0) AS stock
                FROM Producto JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
                ORDER BY Producto.activo DESC, Producto.nombre ASC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $productos = [];
        while ($producto = $resultado->fetch_assoc()) {
            $productos[] = $producto;
        }
        $consulta->close();
        return $productos;
    }

    public function obtenerParaAdmin($idProducto) {
        $consulta = $this->conexion->prepare("SELECT * FROM Producto WHERE id_producto = ?");
        $consulta->bind_param("i", $idProducto);
        $consulta->execute();
        $producto = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        if ($producto) {
            $producto["talles"] = $this->listarTalles($idProducto);
        }
        return $producto;
    }

    public function crear($datos) {
        try {
            $this->conexion->begin_transaction();
            $consulta = $this->conexion->prepare(
                "INSERT INTO Producto (nombre, descripcion, precio, imagen, stock_minimo, id_categoria, activo)
                 VALUES (?, ?, ?, ?, ?, ?, 1)"
            );
            $consulta->bind_param("ssdsii", $datos["nombre"], $datos["descripcion"], $datos["precio"],
                $datos["imagen"], $datos["stock_minimo"], $datos["id_categoria"]);
            $consulta->execute();
            $id = (int)$this->conexion->insert_id;
            $consulta->close();
            $this->guardarTalles($id, $datos["talles"]);
            $this->conexion->commit();
            return $id;
        } catch (mysqli_sql_exception $error) {
            $this->conexion->rollback();
            return false;
        }
    }

    public function actualizar($id, $datos) {
        try {
            $this->conexion->begin_transaction();
            $consulta = $this->conexion->prepare(
                "UPDATE Producto SET nombre = ?, descripcion = ?, precio = ?, imagen = ?,
                    stock_minimo = ?, id_categoria = ? WHERE id_producto = ?"
            );
            $consulta->bind_param("ssdsiii", $datos["nombre"], $datos["descripcion"], $datos["precio"],
                $datos["imagen"], $datos["stock_minimo"], $datos["id_categoria"], $id);
            $consulta->execute();
            $consulta->close();
            $this->guardarTalles($id, $datos["talles"]);
            $this->conexion->commit();
            return true;
        } catch (mysqli_sql_exception $error) {
            $this->conexion->rollback();
            return false;
        }
    }

    // Conserva los talles existentes y agrega los nuevos, sin borrar referencias de pedidos.
    private function guardarTalles($id, $talles) {
        foreach ($talles as $fila) {
            $consulta = $this->conexion->prepare("SELECT talle FROM Producto_Talle WHERE id_producto = ? AND talle = ?");
            $consulta->bind_param("is", $id, $fila["talle"]);
            $consulta->execute();
            $existe = $consulta->get_result()->fetch_assoc();
            $consulta->close();
            if ($existe) {
                $consulta = $this->conexion->prepare("UPDATE Producto_Talle SET stock = ? WHERE id_producto = ? AND talle = ?");
                $consulta->bind_param("iis", $fila["stock"], $id, $fila["talle"]);
            } else {
                $consulta = $this->conexion->prepare("INSERT INTO Producto_Talle (id_producto, talle, stock) VALUES (?, ?, ?)");
                $consulta->bind_param("isi", $id, $fila["talle"], $fila["stock"]);
            }
            $consulta->execute();
            $consulta->close();
        }
    }

    public function cambiarActivo($id, $activo) {
        $consulta = $this->conexion->prepare("UPDATE Producto SET activo = ? WHERE id_producto = ?");
        $consulta->bind_param("ii", $activo, $id);
        $correcto = $consulta->execute();
        $consulta->close();
        return $correcto;
    }

    // Los simbolos % y _ escritos por el usuario se buscan como texto literal.
    private function escaparLike($texto) {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $texto);
    }
}
