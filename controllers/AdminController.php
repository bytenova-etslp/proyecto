<?php
require_once __DIR__ . "/../models/ProductoModel.php";

class AdminController {
    private $productoModel;

    public function __construct($conexion) {
        requerirRol("administrador");
        $this->productoModel = new ProductoModel($conexion);
    }

    public function panel() {
        $nombreAdmin = usuarioActualNombre();
        $productos = $this->productoModel->listarProductos();
        $cantidadProductos = count($productos);
        $unidadesDisponibles = 0;
        $productosStockBajo = [];

        foreach ($productos as $producto) {
            $unidadesDisponibles += (int)$producto["stock"];
            // El ESRE define la alerta cuando el total es menor que el minimo.
            if ((int)$producto["stock"] < (int)$producto["stock_minimo"]) {
                $productosStockBajo[] = $producto;
            }
        }
        require __DIR__ . "/../views/admin.php";
    }
}
