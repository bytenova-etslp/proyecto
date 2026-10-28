<?php
$tituloPagina = "Punto Moda - Administración";
$seccionActiva = "admin";
$estiloExtra = "css/admin.css";
require __DIR__ . "/header.php";
?>
<main>
    <h1 class="titulo-seccion">Panel de administración</h1>
    <p>Bienvenido, <strong><?php echo escapar($nombreAdmin); ?></strong>.</p>

    <p class="acciones-admin">
        <a href="index.php?pagina=admin_productos" class="btn">Gestionar productos</a>
        <a href="index.php?pagina=admin_pedidos" class="btn">Pedidos recibidos</a>
        <a href="index.php?pagina=admin_informe" class="btn">Informe de transacciones</a>
        <?php if (esAdmin()): ?>
            <a href="index.php?pagina=admin_usuarios" class="btn">Gestionar usuarios</a>
        <?php endif; ?>
    </p>

    <section class="tarjetas" aria-label="Resumen del catálogo">
        <article class="tarjeta">
            <h2>Productos activos</h2>
            <p><?php echo (int)$cantidadProductos; ?></p>
        </article>
        <article class="tarjeta">
            <h2>Unidades disponibles</h2>
            <p><?php echo (int)$unidadesDisponibles; ?></p>
        </article>
        <article class="tarjeta">
            <h2>Productos con stock bajo</h2>
            <p><?php echo count($productosStockBajo); ?></p>
        </article>
    </section>

    <section>
        <h2>Alertas de stock bajo</h2>
        <p>Se suman las existencias de todos los talles de cada producto activo.
           La alerta aparece cuando ese total es menor que su stock mínimo.</p>

        <?php if (empty($productosStockBajo)): ?>
            <p>No hay productos con stock por debajo del mínimo.</p>
        <?php else: ?>
            <div class="contenedor-tabla">
                <table class="tabla">
                    <thead>
                        <tr><th>ID</th><th>Producto</th><th>Stock total</th><th>Stock mínimo</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productosStockBajo as $producto): ?>
                            <tr>
                                <td><?php echo (int)$producto["id_producto"]; ?></td>
                                <td><?php echo escapar($producto["nombre"]); ?></td>
                                <td><?php echo (int)$producto["stock"]; ?></td>
                                <td><?php echo (int)$producto["stock_minimo"]; ?></td>
                                <td><a href="index.php?pagina=producto&id=<?php echo (int)$producto["id_producto"]; ?>">Ver producto</a> <a href="index.php?pagina=admin_producto_editar&id=<?php echo (int)$producto["id_producto"]; ?>">Editar stock</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
