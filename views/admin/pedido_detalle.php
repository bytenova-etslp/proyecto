<?php
$tituloPagina = "Punto Moda - Detalle del pedido recibido";
$seccionActiva = "gestion_pedidos";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main class="pedidos-gestion">
    <h1 class="titulo-seccion">Pedido #<?php echo escapar($pedido["id_pedido"]); ?></h1>
    <?php if ($avisoCancelacion !== ""): ?><p class="aviso-cancelacion" role="status"><?php echo escapar($avisoCancelacion); ?></p><?php endif; ?>
    <p>Fecha: <?php echo escapar($pedido["fecha"]); ?></p>
    <p>Estado del pedido: <span class="estado-pedido" data-estado="<?php echo escapar($pedido["estado"]); ?>"><?php echo escapar(ucfirst($pedido["estado"])); ?></span></p>
    <p>Medio de pago: <?php echo escapar($pedido["metodo_pago"] === "tarjeta_ficticia" ? "Tarjeta" : "Por definir"); ?></p>
    <div class="resumen-pedido-gestion">
        <section>
            <h2>Cliente</h2>
            <p><?php echo escapar($pedido["cliente_nombre"]); ?></p>
            <p><?php echo escapar($pedido["cliente_email"]); ?></p>
        </section>
        <?php require __DIR__ . "/../envio_resumen.php"; ?>
    </div>
    <div class="contenedor-tabla">
        <table class="tabla">
            <caption>Productos del pedido</caption>
            <thead><tr><th>Producto</th><th>Talle</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($detalle as $linea): ?>
                    <tr>
                        <td><?php echo escapar($linea["nombre"]); ?></td>
                        <td><?php echo escapar($linea["talle"]); ?></td>
                        <td><?php echo escapar($linea["cantidad"]); ?></td>
                        <td>$<?php echo escapar(number_format((float)$linea["precio_unitario"], 2, ",", ".")); ?></td>
                        <td>$<?php echo escapar(number_format((int)$linea["cantidad"] * (float)$linea["precio_unitario"], 2, ",", ".")); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="total-pedido-gestion">Total: $<?php echo escapar(number_format((float)$pedido["total"], 2, ",", ".")); ?></p>
    <?php if ($pedido["estado"] === "pagado"): ?>
        <p class="acciones-admin">
            <a class="btn" href="index.php?pagina=admin_pedido_factura&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver factura</a>
            <a class="btn btn-secundario" href="index.php?pagina=admin_pedido_factura_pdf&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Descargar PDF</a>
        </p>
    <?php endif; ?>
    <?php require __DIR__ . "/../cancelar_pedido.php"; ?>
    <p class="acciones-admin"><a class="btn btn-secundario" href="index.php?pagina=admin_pedidos">Volver a pedidos recibidos</a></p>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
