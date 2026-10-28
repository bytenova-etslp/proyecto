<?php
$tituloPagina = "Punto Moda - Resultado del pago";
$seccionActiva = "mis_pedidos";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <?php if ($pedido["estado"] === "pagado"): ?>
            <h2 class="titulo-seccion">Pago aprobado</h2>
            <p>Tu pedido #<?php echo escapar($pedido["id_pedido"]); ?> está pagado. Gracias por tu compra.</p>
            <a class="btn" href="index.php?pagina=factura&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver factura</a>
        <?php else: ?>
            <h2 class="titulo-seccion">Pago rechazado</h2>
            <p>No pudimos procesar el pago del pedido #<?php echo escapar($pedido["id_pedido"]); ?>. Revisá los datos y volvé a intentarlo.</p>
            <a class="btn" href="index.php?pagina=pago&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Reintentar pago</a>
        <?php endif; ?>
        <p>Total del pedido: <strong>$<?php echo escapar(number_format($total, 2, ",", ".")); ?></strong></p>
        <a class="btn btn-secundario" href="index.php?pagina=pedido&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver detalle del pedido</a>
        <a class="btn btn-secundario" href="index.php?pagina=mis_pedidos">Mis pedidos</a>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
