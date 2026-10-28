<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo escapar($factura["numero"]); ?> - Punto Moda</title>
    <link rel="stylesheet" href="css/factura.css?v=17">
</head>
<body>
    <nav class="barra-factura" aria-label="Acciones de la factura">
        <button class="boton-factura" type="button" id="imprimir-factura">Imprimir / Guardar PDF</button>
        <a class="volver-factura" href="<?php echo escapar($urlVolver); ?>">Volver al pedido</a>
        <p class="ayuda-impresion">En la ventana de impresión podés elegir Guardar como PDF.</p>
    </nav>
    <main class="hoja-factura">
        <header class="cabecera-factura">
            <div><p class="marca-factura">Punto <span>Moda</span></p><p>Indumentaria</p></div>
            <div class="numero-factura"><h1>Factura</h1><p>N.º <?php echo escapar($factura["numero"]); ?></p></div>
        </header>
        <p class="pago-confirmado">Pago aprobado · Gracias por tu compra</p>
        <dl class="referencias-factura">
            <div><dt>Pedido</dt><dd>#<?php echo escapar($pedido["id_pedido"]); ?></dd></div>
            <div><dt>Fecha del pedido</dt><dd><?php echo escapar($pedido["fecha"]); ?></dd></div>
            <div><dt>Fecha de pago</dt><dd><?php echo escapar($pago["fecha"]); ?></dd></div>
            <div><dt>Medio de pago</dt><dd>Tarjeta · **** <?php echo escapar($pago["ultimos4"]); ?></dd></div>
        </dl>
        <div class="datos-factura">
            <section>
                <h2>Cliente</h2>
                <p><?php echo escapar($pedido["cliente_nombre"]); ?></p>
                <p><?php echo escapar($pedido["cliente_email"]); ?></p>
                <?php if (!empty($pedido["envio_telefono"])): ?><p>Teléfono: <?php echo escapar(formatearTelefono($pedido["envio_telefono"])); ?></p><?php endif; ?>
            </section>
            <section>
                <h2>Datos de envío</h2>
                <?php if (!empty($pedido["envio_direccion"])): ?>
                    <address>
                        <span><?php echo escapar($pedido["envio_nombre"]); ?></span>
                        <span><?php echo escapar($pedido["envio_direccion"]); ?></span>
                        <span><?php echo escapar($pedido["envio_ciudad"]); ?>, <?php echo escapar($pedido["envio_departamento"]); ?></span>
                    </address>
                <?php else: ?><p>Sin datos de envío registrados.</p><?php endif; ?>
            </section>
        </div>
        <div class="tabla-factura-contenedor">
            <table class="tabla-factura">
                <caption>Detalle de la compra</caption>
                <thead><tr><th scope="col">Producto</th><th scope="col">Talle</th><th scope="col" class="numero">Cant.</th><th scope="col" class="numero">Precio unitario</th><th scope="col" class="numero">Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($detalle as $linea): ?>
                        <tr>
                            <td><?php echo escapar($linea["nombre"]); ?></td>
                            <td><?php echo escapar($linea["talle"]); ?></td>
                            <td class="numero"><?php echo escapar($linea["cantidad"]); ?></td>
                            <td class="numero">$<?php echo escapar(number_format($linea["precio_centavos"] / 100, 2, ",", ".")); ?></td>
                            <td class="numero">$<?php echo escapar(number_format($linea["subtotal_centavos"] / 100, 2, ",", ".")); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="total-factura"><span>Total pagado</span><strong>$<?php echo escapar(number_format($factura["total_centavos"] / 100, 2, ",", ".")); ?></strong></div>
        <footer class="pie-factura"><p>Importes expresados en pesos uruguayos (UYU).</p><p>Conservá este documento como constancia de tu compra.</p></footer>
    </main>
    <script src="js/factura.js?v=17"></script>
</body>
</html>
