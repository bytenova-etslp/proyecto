<?php
$tituloPagina = "Punto Moda - Pedido";
$seccionActiva = "mis_pedidos";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <h2 class="titulo-seccion">Pedido #<?php echo escapar($pedido["id_pedido"]); ?></h2>
        <?php if ($avisoCancelacion !== ""): ?><p class="aviso-cancelacion" role="status"><?php echo escapar($avisoCancelacion); ?></p><?php endif; ?>
        <p>Fecha: <?php echo escapar($pedido["fecha"]); ?></p>
        <p>Estado: <strong><?php echo escapar(ucfirst($pedido["estado"])); ?></strong></p>
        <p>Medio de pago: <?php echo escapar($pedido["metodo_pago"] === "tarjeta_ficticia" ? "Tarjeta" : "Por definir"); ?></p>
        <?php require __DIR__ . "/envio_resumen.php"; ?>
        <div class="tabla-pedido-contenedor">
            <table class="tabla-pedido">
                <caption>Detalle guardado del pedido</caption>
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
        <p class="total-carrito">Total: $<?php echo escapar(number_format($total, 2, ",", ".")); ?></p>
        <p>Los importes corresponden a los precios vigentes al registrar el pedido.</p>
        <?php if ($pedido["estado"] === "pagado"): ?>
            <a class="btn" href="index.php?pagina=factura&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver factura</a>
        <?php endif; ?>
        <?php if (in_array($pedido["estado"], ["pendiente", "rechazado"], true)): ?>
            <a class="btn" href="index.php?pagina=pago&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>"><?php echo $pedido["estado"] === "rechazado" ? "Reintentar pago" : "Pagar con tarjeta"; ?></a>
        <?php endif; ?>
        <?php if (!empty($pagos)): ?>
            <div class="tabla-pedido-contenedor">
                <table class="tabla-pedido">
                    <caption>Historial de pagos</caption>
                    <thead><tr><th>Intento</th><th>Fecha</th><th>Resultado</th><th>Importe</th><th>Terminacion</th></tr></thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo escapar($pago["id_pago"]); ?></td>
                                <td><?php echo escapar($pago["fecha"]); ?></td>
                                <td><?php echo escapar($pago["resultado"]); ?></td>
                                <td>$<?php echo escapar(number_format((float)$pago["monto"], 2, ",", ".")); ?></td>
                                <td>**** <?php echo escapar($pago["ultimos4"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php require __DIR__ . "/cancelar_pedido.php"; ?>
        <a class="btn btn-secundario" href="index.php?pagina=mis_pedidos">Mis pedidos</a>
        <a class="btn" href="index.php?pagina=productos">Seguir comprando</a>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
