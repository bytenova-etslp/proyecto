<?php
require_once __DIR__ . "/../models/ProductoModel.php";
require_once __DIR__ . "/../config/imagenes.php";

class AdminProductoController {
    private $productoModel;

    public function __construct($conexion) {
        requerirRol(["empleado", "administrador"]);
        $this->productoModel = new ProductoModel($conexion);
    }

    public function listar() {
        $productos = $this->productoModel->listarTodosAdmin();
        foreach ($productos as $indice => $producto) {
            $productos[$indice]["talles"] = $this->productoModel->listarTalles($producto["id_producto"]);
        }
        $avisos = [
            "creado" => "Producto creado correctamente.",
            "editado" => "Producto actualizado correctamente.",
            "desactivado" => "El producto fue dado de baja y ya no aparece en el catálogo.",
            "reactivado" => "El producto volvió a estar disponible.",
            "no_existe" => "No se encontró el producto solicitado.",
            "error" => "No se pudo completar la operación. Intentá nuevamente."
        ];
        $clave = $_GET["msg"] ?? "";
        $aviso = is_string($clave) && isset($avisos[$clave]) ? $avisos[$clave] : "";
        require __DIR__ . "/../views/admin/productos_lista.php";
    }

    public function crear() {
        $producto = $this->productoVacio();
        $errores = [];
        $tallesProtegidos = [];
        $categorias = $this->productoModel->listarCategorias();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $producto = $this->leerFormulario();
            $errores = $this->validar($producto, $categorias);
            if (empty($errores)) {
                $imagen = guardarImagenProducto($_FILES["imagen"] ?? null);
                if (!$imagen["ok"]) {
                    $errores[] = $imagen["error"];
                } else {
                    $producto["imagen"] = $imagen["nombre"] ?? "imagenes/productos/sin-imagen.svg";
                    if ($this->productoModel->crear($producto)) {
                        redirigir("admin_productos", "&msg=creado");
                    }
                    borrarImagenProducto($imagen["nombre"]);
                    $producto["imagen"] = "";
                    $errores[] = "No se pudo guardar el producto. Intentá nuevamente.";
                }
            }
        }
        $titulo = "Nuevo producto";
        $urlAccion = "index.php?pagina=admin_producto_crear";
        require __DIR__ . "/../views/admin/producto_form.php";
    }

    public function editar() {
        $id = $this->leerId();
        $actual = $id ? $this->productoModel->obtenerParaAdmin($id) : null;
        if (!$actual) {
            redirigir("admin_productos", "&msg=no_existe");
        }
        $producto = $actual;
        $errores = [];
        $tallesProtegidos = array_column($actual["talles"], "talle");
        $categorias = $this->productoModel->listarCategorias();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $producto = $this->leerFormulario();
            $producto["imagen"] = $actual["imagen"];
            $errores = $this->validar($producto, $categorias, $actual["talles"]);
            if (empty($errores)) {
                $imagen = guardarImagenProducto($_FILES["imagen"] ?? null);
                if (!$imagen["ok"]) {
                    $errores[] = $imagen["error"];
                } else {
                    if ($imagen["nombre"] !== null) {
                        $producto["imagen"] = $imagen["nombre"];
                    }
                    if ($this->productoModel->actualizar($id, $producto)) {
                        if ($imagen["nombre"] !== null) {
                            borrarImagenProducto($actual["imagen"]);
                        }
                        redirigir("admin_productos", "&msg=editado");
                    }
                    borrarImagenProducto($imagen["nombre"]);
                    $producto["imagen"] = $actual["imagen"];
                    $errores[] = "No se pudo actualizar el producto. Intentá nuevamente.";
                }
            }
        }
        $titulo = "Editar producto";
        $urlAccion = "index.php?pagina=admin_producto_editar&id=" . (int)$id;
        require __DIR__ . "/../views/admin/producto_form.php";
    }

    public function cambiarEstado() {
        $id = $this->leerId();
        $producto = $id ? $this->productoModel->obtenerParaAdmin($id) : null;
        if (!$producto) {
            redirigir("admin_productos", "&msg=no_existe");
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $valor = $_POST["activo"] ?? "";
            if (!is_string($valor) || !in_array($valor, ["0", "1"], true)) {
                redirigir("admin_productos", "&msg=error");
            }
            $activo = (int)$valor;
            if ($this->productoModel->cambiarActivo($id, $activo)) {
                redirigir("admin_productos", $activo ? "&msg=reactivado" : "&msg=desactivado");
            }
            redirigir("admin_productos", "&msg=error");
        }
        // GET muestra la confirmacion; solo POST cambia la base.
        $activo = (int)$producto["activo"] === 1 ? 0 : 1;
        require __DIR__ . "/../views/admin/producto_estado.php";
    }

    private function leerId() {
        $id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);
        return $id !== false && $id > 0 ? $id : null;
    }

    private function productoVacio() {
        return [
            "nombre" => "", "descripcion" => "", "precio" => "", "imagen" => "",
            "stock_minimo" => "5", "id_categoria" => "", "datos_validos" => true,
            "talles" => [
                ["talle" => "S", "stock" => "0"], ["talle" => "M", "stock" => "0"],
                ["talle" => "L", "stock" => "0"], ["talle" => "XL", "stock" => "0"]
            ]
        ];
    }

    private function leerFormulario() {
        $producto = $this->productoVacio();
        foreach (["nombre", "descripcion", "precio", "stock_minimo", "id_categoria"] as $campo) {
            $valor = $_POST[$campo] ?? "";
            if (!is_string($valor)) {
                $producto["datos_validos"] = false;
                $valor = "";
            }
            $producto[$campo] = trim($valor);
        }
        $nombres = $_POST["talles"] ?? [];
        $stocks = $_POST["stocks"] ?? [];
        $producto["talles"] = [];
        if (!is_array($nombres) || !is_array($stocks) || count($nombres) !== count($stocks)) {
            $producto["datos_validos"] = false;
            return $producto;
        }
        $nombres = array_values($nombres);
        $stocks = array_values($stocks);
        foreach ($nombres as $indice => $nombre) {
            if (!is_string($nombre) || !is_string($stocks[$indice])) {
                $producto["datos_validos"] = false;
                continue;
            }
            $nombre = trim($nombre);
            $stock = trim($stocks[$indice]);
            if ($nombre !== "" || $stock !== "") {
                $producto["talles"][] = ["talle" => $nombre, "stock" => $stock];
            }
        }
        return $producto;
    }

    private function validar($producto, $categorias, $tallesActuales = []) {
        $errores = [];
        if (!$producto["datos_validos"]) {
            return ["Los datos del formulario no son válidos."];
        }
        if (preg_match('/^.{1,100}$/us', $producto["nombre"]) !== 1) {
            $errores[] = "El nombre es obligatorio y admite hasta 100 caracteres.";
        }
        if (preg_match('/^.{0,500}$/us', $producto["descripcion"]) !== 1) {
            $errores[] = "La descripción admite hasta 500 caracteres.";
        }
        $precio = filter_var($producto["precio"], FILTER_VALIDATE_FLOAT);
        if ($precio === false || !is_finite($precio) || $precio < 0 || $precio > 99999999.99 || round($precio, 2) != $precio) {
            $errores[] = "El precio debe ser un número entre 0 y 99999999.99, con hasta dos decimales.";
        }
        $categoria = filter_var($producto["id_categoria"], FILTER_VALIDATE_INT);
        if ($categoria === false || !in_array($categoria, array_map("intval", array_column($categorias, "id_categoria")), true)) {
            $errores[] = "Seleccioná una categoría existente.";
        }
        $minimo = filter_var($producto["stock_minimo"], FILTER_VALIDATE_INT);
        if ($minimo === false || $minimo < 0 || $minimo > 2147483647) {
            $errores[] = "El stock mínimo debe ser un entero entre 0 y 2147483647.";
        }
        if (empty($producto["talles"])) {
            $errores[] = "Agregá al menos un talle con su stock.";
        }
        $nombres = [];
        foreach ($producto["talles"] as $fila) {
            $clave = strtoupper($fila["talle"]);
            if (preg_match('/^.{1,10}$/u', $fila["talle"]) !== 1) {
                $errores[] = "Cada talle debe tener entre 1 y 10 caracteres.";
            } elseif (in_array($clave, $nombres, true)) {
                $errores[] = "Los talles no pueden estar repetidos.";
            }
            $nombres[] = $clave;
            $stock = filter_var($fila["stock"], FILTER_VALIDATE_INT);
            if ($stock === false || $stock < 0 || $stock > 2147483647) {
                $errores[] = "El stock de cada talle debe ser un entero entre 0 y 2147483647.";
            }
        }
        foreach ($tallesActuales as $fila) {
            if (!in_array(strtoupper($fila["talle"]), $nombres, true)) {
                $errores[] = "Los talles existentes se conservan. Para agotarlos, indicá stock 0.";
                break;
            }
        }
        return array_unique($errores);
    }
}
