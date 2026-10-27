<section class="resumen-envio" aria-label="Datos de envío">
    <h3>Datos de envío</h3>
    <?php if (!empty($pedido["envio_direccion"])): ?>
        <address>
            <span><?php echo escapar($pedido["envio_nombre"]); ?></span>
            <span><?php echo escapar($pedido["envio_direccion"]); ?></span>
            <span><?php echo escapar($pedido["envio_ciudad"]); ?>, <?php echo escapar($pedido["envio_departamento"]); ?></span>
            <span>Teléfono: <?php echo escapar(formatearTelefono($pedido["envio_telefono"])); ?></span>
        </address>
    <?php else: ?>
        <p>Sin datos de envío registrados.</p>
    <?php endif; ?>
</section>
