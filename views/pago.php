<?php
$tituloPagina = "Punto Moda - Pago";
$seccionActiva = "mis_pedidos";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <h2 class="titulo-seccion">Finalizar pago</h2>
        <p>Completá los datos de tu tarjeta para pagar el pedido.</p>
        <?php if ($pedido["estado"] === "rechazado"): ?>
            <p class="errores-pago" role="alert">El pago anterior fue rechazado. Revisá los datos de tu tarjeta y volvé a intentarlo.</p>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <ul class="errores-pago" role="alert">
                <?php foreach ($errores as $error): ?><li><?php echo escapar($error); ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div class="pago-distribucion">
            <form class="formulario-pago" action="index.php?pagina=pago&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>" method="post" autocomplete="off">
                <h3>Pago con tarjeta</h3>
                <div class="campo-pago">
                    <label for="titular">Nombre del titular</label>
                    <input id="titular" name="titular" type="text" minlength="3" maxlength="60" value="<?php echo escapar($titular); ?>" required>
                </div>
                <div class="campo-pago">
                    <label for="numero">Número de tarjeta</label>
                    <input id="numero" name="numero" type="text" inputmode="numeric" maxlength="19" autocomplete="off" required>
                </div>
                <div class="fila-pago">
                    <div class="campo-pago">
                        <label for="vencimiento">Vencimiento</label>
                        <input id="vencimiento" name="vencimiento" type="text" inputmode="numeric" maxlength="5" placeholder="MM/AA" autocomplete="off" required>
                    </div>
                    <div class="campo-pago">
                        <label for="cvv">Código de seguridad</label>
                        <input id="cvv" name="cvv" type="password" inputmode="numeric" maxlength="3" autocomplete="off" required>
                    </div>
                </div>
                <button class="btn" type="submit">Pagar $<?php echo escapar(number_format($total, 2, ",", ".")); ?></button>
            </form>
            <aside class="resumen-pago" aria-label="Resumen del pedido">
                <h3>Resumen del pedido</h3>
                <p>Pedido #<?php echo escapar($pedido["id_pedido"]); ?></p>
                <dl>
                    <div><dt>Medio de pago</dt><dd>Tarjeta</dd></div>
                    <div class="importe-pago"><dt>Total a pagar</dt><dd>$<?php echo escapar(number_format($total, 2, ",", ".")); ?></dd></div>
                </dl>
                <a href="index.php?pagina=pedido&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Volver al detalle del pedido</a>
            </aside>
        </div>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
