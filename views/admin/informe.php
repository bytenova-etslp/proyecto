<?php
$tituloPagina = "Punto Moda - Informe de transacciones";
$seccionActiva = "informe";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main class="informe-transacciones">
    <h1 class="titulo-seccion">Informe de transacciones</h1>
    <div class="acciones-admin">
        <a class="btn btn-secundario" href="index.php?pagina=admin">Volver al panel</a>
        <a class="btn btn-secundario" href="index.php?pagina=admin_pedidos">Pedidos recibidos</a>
    </div>
    <form action="index.php" method="get" class="filtros-informe">
        <input type="hidden" name="pagina" value="admin_informe">
        <div><label for="desde">Desde</label><input type="date" id="desde" name="desde" min="1000-01-01" max="9998-12-31" value="<?php echo escapar($desde); ?>" required></div>
        <div><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" min="1000-01-01" max="9998-12-31" value="<?php echo escapar($hasta); ?>" required></div>
        <button class="btn" type="submit">Generar informe</button>
    </form>
    <?php if (!empty($errores)): ?>
        <ul class="errores-admin" role="alert">
            <?php foreach ($errores as $error): ?><li><?php echo escapar($error); ?></li><?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Período: <strong><?php echo escapar($desde); ?></strong> al <strong><?php echo escapar($hasta); ?></strong>, inclusive.</p>
        <section class="tarjetas" aria-label="Resumen de transacciones">
            <article class="tarjeta"><h2>Ventas cobradas</h2><p><?php echo escapar($resumen["aprobados"]); ?></p><small>Pagos aprobados</small></article>
            <article class="tarjeta"><h2>Total cobrado</h2><p>$<?php echo escapar(number_format((float)$resumen["total_cobrado"], 2, ",", ".")); ?></p><small>Pesos uruguayos (UYU)</small></article>
            <article class="tarjeta"><h2>Promedio por venta</h2><p>$<?php echo escapar(number_format($promedio, 2, ",", ".")); ?></p><small>Solo pagos aprobados</small></article>
            <article class="tarjeta"><h2>Intentos rechazados</h2><p><?php echo escapar($resumen["rechazados"]); ?></p><small>No suman al total cobrado</small></article>
        </section>
        <p>El período corresponde a la fecha de cada pago. Los pedidos pendientes sin intentos de pago no aparecen. Un reintento rechazado se registra por separado; solo el pago aprobado suma como venta.</p>
        <h2>Detalle de transacciones</h2>
        <?php if (empty($pagos)): ?>
            <p class="aviso-admin">No hubo transacciones en el período elegido.</p>
        <?php else: ?>
            <div class="contenedor-tabla">
                <table class="tabla">
                    <caption><?php echo escapar($resumen["intentos"]); ?> transacciones en el período</caption>
                    <thead><tr><th scope="col">Pago</th><th scope="col">Pedido</th><th scope="col">Fecha del pago</th><th scope="col">Cliente</th><th scope="col">Correo electrónico</th><th scope="col">Resultado</th><th scope="col">Importe del intento</th><th scope="col">Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td>#<?php echo escapar($pago["id_pago"]); ?></td>
                                <td>#<?php echo escapar($pago["id_pedido"]); ?></td>
                                <td><?php echo escapar($pago["fecha"]); ?></td>
                                <td><?php echo escapar($pago["cliente_nombre"]); ?></td>
                                <td class="correo-usuario"><?php echo escapar($pago["cliente_email"]); ?></td>
                                <td><?php echo escapar(ucfirst($pago["resultado"])); ?></td>
                                <td class="importe-pedido">$<?php echo escapar(number_format((float)$pago["monto"], 2, ",", ".")); ?></td>
                                <td>
                                    <a href="index.php?pagina=admin_pedido&amp;id=<?php echo escapar($pago["id_pedido"]); ?>">Ver pedido</a>
                                    <?php if ($pago["resultado"] === "aprobado" && $pago["estado_pedido"] === "pagado"): ?>
                                        <a href="index.php?pagina=admin_pedido_factura&amp;id=<?php echo escapar($pago["id_pedido"]); ?>">Factura</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav class="paginacion-informe" aria-label="Páginas del informe">
                <?php if ($urlAnterior !== ""): ?><a class="btn btn-secundario" href="<?php echo escapar($urlAnterior); ?>">Anterior</a><?php endif; ?>
                <span>Página <?php echo escapar($numero); ?> de <?php echo escapar($paginas); ?></span>
                <?php if ($urlSiguiente !== ""): ?><a class="btn btn-secundario" href="<?php echo escapar($urlSiguiente); ?>">Siguiente</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
