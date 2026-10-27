<?php
$tituloPagina = "Punto Moda - Datos de envío";
$seccionActiva = "carrito";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <h2 class="titulo-seccion">Datos de envío</h2>
        <p>Indicá quién recibe el pedido y dónde lo entregamos.</p>
        <div class="pago-distribucion">
            <form id="form-checkout" class="formulario-pago" action="index.php?pagina=confirmar_pedido" method="post">
                <h3>Dirección y contacto</h3>
                <input type="hidden" name="carrito" value="<?php echo escapar($carritoJson); ?>">
                <div class="campo-pago">
                    <label for="envio-nombre">Nombre del destinatario</label>
                    <input id="envio-nombre" name="envio_nombre" type="text" minlength="3" maxlength="60" autocomplete="shipping name" value="<?php echo escapar($nombreDestinatario); ?>" required>
                </div>
                <div class="campo-pago">
                    <label for="envio-direccion">Dirección</label>
                    <input id="envio-direccion" name="envio_direccion" type="text" minlength="5" maxlength="100" autocomplete="shipping street-address" placeholder="Calle, número y apartamento" required>
                </div>
                <div class="fila-pago">
                    <div class="campo-pago">
                        <label for="envio-ciudad">Ciudad o localidad</label>
                        <input id="envio-ciudad" name="envio_ciudad" type="text" minlength="2" maxlength="50" autocomplete="shipping address-level2" required>
                    </div>
                    <div class="campo-pago">
                        <label for="envio-departamento">Departamento</label>
                        <select id="envio-departamento" name="envio_departamento" autocomplete="shipping address-level1" required>
                            <option value="">Seleccioná</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo escapar($departamento); ?>"><?php echo escapar($departamento); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="campo-pago">
                    <label for="envio-telefono">Teléfono de contacto</label>
                    <input id="envio-telefono" name="envio_telefono" type="tel" inputmode="numeric" minlength="9" maxlength="11" pattern="[0-9]{3} ?[0-9]{3} ?[0-9]{3}" placeholder="099 999 999" autocomplete="shipping tel-national" required aria-describedby="ayuda-telefono">
                    <small id="ayuda-telefono">9 dígitos, sin prefijo de país. Podés escribirlos juntos o en grupos de tres.</small>
                </div>
                <p id="mensaje-checkout" role="status" aria-live="polite"></p>
                <button class="btn" type="submit">Confirmar pedido y continuar al pago</button>
            </form>
            <aside class="resumen-pago" aria-label="Resumen de la compra">
                <h3>Resumen de tu compra</h3>
                <ul class="lineas-checkout">
                    <?php foreach ($resumen["lineas"] as $linea): ?>
                        <li>
                            <strong><?php echo escapar($linea["nombre"]); ?></strong>
                            <span>Talle <?php echo escapar($linea["talle"]); ?> · Cantidad: <?php echo escapar($linea["cantidad"]); ?></span>
                            <span>$<?php echo escapar(number_format((float)$linea["subtotal"], 2, ",", ".")); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <dl><div class="importe-pago"><dt>Total</dt><dd>$<?php echo escapar(number_format($resumen["total"], 2, ",", ".")); ?></dd></div></dl>
                <p>El importe definitivo se calcula con los precios vigentes al confirmar.</p>
                <a href="index.php?pagina=carrito">Volver al carrito</a>
            </aside>
        </div>
    </section>
</main>
<?php
$scriptExtra = "js/checkout.js";
require __DIR__ . "/footer.php";
?>
