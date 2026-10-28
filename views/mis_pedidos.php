<?php
$tituloPagina = "Punto Moda - Mis pedidos";
$seccionActiva = "mis_pedidos";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <h2 class="titulo-seccion">Mis pedidos</h2>
        <?php if (empty($pedidos)): ?>
            <p>Todavia no tienes pedidos registrados.</p>
            <a class="btn" href="index.php?pagina=productos">Ver productos</a>
        <?php else: ?>
            <div class="tabla-pedido-contenedor">
                <table class="tabla-pedido">
                    <caption>Pedidos de tu cuenta</caption>
                    <thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th>Total</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td>#<?php echo escapar($pedido["id_pedido"]); ?></td>
                                <td><?php echo escapar($pedido["fecha"]); ?></td>
                                <td><?php echo escapar(ucfirst($pedido["estado"])); ?></td>
                                <td>$<?php echo escapar(number_format((float)$pedido["total"], 2, ",", ".")); ?></td>
                                <td>
                                    <a href="index.php?pagina=pedido&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver detalle</a>
                                    <?php if ($pedido["estado"] === "pagado"): ?>
                                        | <a href="index.php?pagina=factura&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Factura</a>
                                    <?php endif; ?>
                                    <?php if (in_array($pedido["estado"], ["pendiente", "rechazado"], true)): ?>
                                        | <a href="index.php?pagina=pago&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Pagar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
