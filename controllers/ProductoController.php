<?php
require_once __DIR__ . "/../models/ProductoModel.php";

class ProductoController {
    private $productoModel;

    public function __construct($conexion) {
        $this->productoModel = new ProductoModel($conexion);
    }

    public function mostrarInicio() {
        $producto = $this->productoModel->obtenerProductoPorId(2);
        require __DIR__ . "/../views/inicio.php";
    }

    public function mostrarCatalogo() {
        $productos = $this->productoModel->listarProductos();
        foreach ($productos as $indice => $producto) {
            $productos[$indice]["talles"] = $this->productoModel->listarTalles($producto["id_producto"]);
            $productos[$indice]["precio_carrito"] = $producto["precio_promocional"] ?? $producto["precio"];
        }
        require __DIR__ . "/../views/productos.php";
    }

    public function mostrarDetalle() {
        $id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);
        $producto = null;
        $talles = [];
        $precioCarrito = 0;

        if ($id !== false && $id > 0) {
            $producto = $this->productoModel->obtenerProductoPorId($id);
        }
        if ($producto) {
            $talles = $this->productoModel->listarTalles($id);
            $precioCarrito = $producto["precio_promocional"] ?? $producto["precio"];
        } else {
            http_response_code(404);
        }
        require __DIR__ . "/../views/detalle-producto.php";
    }
}
