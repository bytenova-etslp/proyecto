<?php
// Consultas de productos y existencias por talle. Devuelve arreglos, sin HTML.
class ProductoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function listarProductos() {
        $sql = "SELECT Producto.id_producto, Producto.nombre, Producto.precio, Producto.stock_minimo,
		(SELECT MIN(Promocion_Producto.precio_promocional)
            FROM Promocion_Producto
            JOIN Promocion ON Promocion_Producto.id_promocion = Promocion.id_promocion
            WHERE Promocion_Producto.id_producto = Producto.id_producto
                AND Promocion.activo = 1
                AND CURRENT_DATE BETWEEN Promocion.fecha_inicio AND Promocion.fecha_fin)
            AS precio_promocional,
		Producto.imagen, COALESCE((SELECT SUM(Producto_Talle.stock)
            FROM Producto_Talle
            WHERE Producto_Talle.id_producto = Producto.id_producto), 0) AS stock, Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		WHERE Producto.activo = 1
		ORDER BY Producto.id_producto ASC";
        $resultado = mysqli_query($this->conexion, $sql);
        $productos = [];
        while ($producto = mysqli_fetch_assoc($resultado)) {
            $productos[] = $producto;
        }
        return $productos;
    }

    public function obtenerProductoPorId($idProducto) {
        $sql = "SELECT Producto.id_producto, Producto.nombre, Producto.descripcion, Producto.precio,
		(SELECT MIN(Promocion_Producto.precio_promocional)
            FROM Promocion_Producto
            JOIN Promocion ON Promocion_Producto.id_promocion = Promocion.id_promocion
            WHERE Promocion_Producto.id_producto = Producto.id_producto
                AND Promocion.activo = 1
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
        $sql = "SELECT talle, stock FROM Producto_Talle WHERE id_producto = ? ORDER BY stock DESC, talle ASC";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idProducto);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $talles = [];
        while ($talle = mysqli_fetch_assoc($resultado)) {
            $talles[] = $talle;
        }
        mysqli_stmt_close($consulta);
        return $talles;
    }
}
