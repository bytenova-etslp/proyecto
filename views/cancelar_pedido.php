<?php if (in_array($pedido["estado"], ["pendiente", "rechazado"], true)): ?>
    <section class="cancelacion-pedido" aria-label="Cancelar la reserva">
        <h2>Cancelar pedido</h2>
        <p>Si no vas a completar esta compra, podés cancelarla para liberar las unidades reservadas. El pedido quedará en el historial y ya no podrá pagarse.</p>
        <form action="<?php echo escapar($urlCancelar); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo escapar($tokenCancelacion); ?>">
            <button class="btn btn-secundario" type="submit">Cancelar pedido</button>
        </form>
    </section>
<?php endif; ?>
