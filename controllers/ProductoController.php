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
        $categorias = $this->productoModel->listarCategorias();
        $idCategoriaSeleccionada = null;
        $busqueda = "";
        $erroresFiltros = [];
        $categoriaRecibida = $_GET["categoria"] ?? "";
        $busquedaRecibida = $_GET["q"] ?? "";

        if (!is_string($categoriaRecibida)) {
            $erroresFiltros[] = "La categoría seleccionada no es válida.";
        } elseif ($categoriaRecibida !== "") {
            $id = filter_var($categoriaRecibida, FILTER_VALIDATE_INT);
            $idsValidos = array_map("intval", array_column($categorias, "id_categoria"));
            if ($id === false || !in_array($id, $idsValidos, true)) {
                $erroresFiltros[] = "Seleccioná una categoría existente.";
            } else {
                $idCategoriaSeleccionada = $id;
            }
        }
        if (!is_string($busquedaRecibida)) {
            $erroresFiltros[] = "El texto de búsqueda no es válido.";
        } else {
            $busqueda = mb_substr(trim($busquedaRecibida), 0, 50);
        }
        $productos = empty($erroresFiltros)
            ? $this->productoModel->listarProductos($idCategoriaSeleccionada, $busqueda)
            : [];
        $urlSinBusqueda = "index.php?pagina=productos";
        if ($idCategoriaSeleccionada !== null) {
            $urlSinBusqueda .= "&categoria=" . $idCategoriaSeleccionada;
        }
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
