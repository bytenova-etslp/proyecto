<?php
$tituloPagina = "Punto Moda - Gestión de productos";
$seccionActiva = "gestion_productos";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main>
    <h1 class="titulo-seccion">Gestión de productos</h1>
    <div class="acciones-admin">
        <a href="index.php?pagina=admin_producto_crear" class="btn">Nuevo producto</a>
        <?php if (esAdmin()): ?>
            <a href="index.php?pagina=admin" class="btn btn-secundario">Volver al panel</a>
        <?php endif; ?>
        <a href="index.php?pagina=productos" class="btn btn-secundario">Ver catálogo</a>
    </div>
    <?php if ($aviso !== ""): ?>
        <p class="aviso-admin" role="status"><?php echo escapar($aviso); ?></p>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p>Todavía no hay productos registrados.</p>
    <?php else: ?>
        <div class="contenedor-tabla">
            <table class="tabla">
                <thead>
                    <tr><th>ID</th><th>Imagen</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Talles y unidades</th><th>Stock total / mínimo</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr class="<?php echo (int)$producto["activo"] === 1 ? "" : "producto-inactivo"; ?>">
                            <td><?php echo (int)$producto["id_producto"]; ?></td>
                            <td><img src="<?php echo escapar($producto["imagen"] ?: "imagenes/productos/sin-imagen.svg"); ?>" alt="" class="miniatura"></td>
                            <td><?php echo escapar($producto["nombre"]); ?></td>
                            <td><?php echo escapar($producto["categoria"]); ?></td>
                            <td>$ <?php echo escapar(number_format($producto["precio"], 2, ",", ".")); ?></td>
                            <td>
                                <?php foreach ($producto["talles"] as $fila): ?>
                                    <span class="talle-stock"><?php echo escapar($fila["talle"]); ?>: <?php echo (int)$fila["stock"]; ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php echo (int)$producto["stock"]; ?> / <?php echo (int)$producto["stock_minimo"]; ?>
                                <?php if ((int)$producto["stock"] === 0): ?>
                                    <strong class="stock-bajo">Agotado</strong>
                                <?php elseif ((int)$producto["stock"] < (int)$producto["stock_minimo"]): ?>
                                    <strong class="stock-bajo">Stock bajo</strong>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int)$producto["activo"] === 1 ? "Activo" : "Desactivado"; ?></td>
                            <td>
                                <a href="index.php?pagina=admin_producto_editar&id=<?php echo (int)$producto["id_producto"]; ?>">Editar</a>
                                <a href="index.php?pagina=admin_producto_estado&id=<?php echo (int)$producto["id_producto"]; ?>"><?php echo (int)$producto["activo"] === 1 ? "Dar de baja" : "Reactivar"; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
