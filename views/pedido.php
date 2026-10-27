<?php
$tituloPagina = "Punto Moda - Pedido";
$seccionActiva = "carrito";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
    <section class="contenedor-carrito confirmacion-pedido">
        <h2 class="titulo-seccion">Pedido #<?php echo escapar($pedido["id_pedido"]); ?> registrado</h2>
        <p>Fecha: <?php echo escapar($pedido["fecha"]); ?></p>
        <p>Estado: <?php echo escapar($pedido["estado"]); ?>. El pago queda por definir.</p>
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
        <a class="btn" href="index.php?pagina=productos">Seguir comprando</a>
    </section>
</main>
<?php require __DIR__ . "/footer.php"; ?>
