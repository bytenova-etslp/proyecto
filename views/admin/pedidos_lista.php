<?php
$tituloPagina = "Punto Moda - Pedidos recibidos";
$seccionActiva = "gestion_pedidos";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main class="pedidos-gestion">
    <h1 class="titulo-seccion">Pedidos recibidos</h1>
    <p>Consultá los pedidos de los clientes y sus datos de envío.</p>
    <form class="filtros-pedidos" action="index.php" method="get">
        <input type="hidden" name="pagina" value="admin_pedidos">
        <label for="estado-pedidos">Estado del pago</label>
        <select id="estado-pedidos" name="estado">
            <option value="">Todos</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo escapar($estado); ?>" <?php if ($estadoSeleccionado === $estado) echo 'selected'; ?>><?php echo escapar(ucfirst($estado)); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn btn-secundario" href="index.php?pagina=admin_pedidos">Ver todos</a>
    </form>
    <p><?php echo escapar(count($pedidos)); ?> pedido(s) encontrado(s).</p>
    <?php if (empty($pedidos)): ?>
        <p class="aviso-admin">No hay pedidos para mostrar.</p>
    <?php else: ?>
        <div class="contenedor-tabla">
            <table class="tabla">
                <caption>Pedidos de todos los clientes</caption>
                <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Correo electrónico</th><th>Estado del pago</th><th>Total</th><th>Envío y contacto</th><th>Detalle</th></tr></thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo escapar($pedido["id_pedido"]); ?></td>
                            <td><?php echo escapar($pedido["fecha"]); ?></td>
                            <td><strong><?php echo escapar($pedido["cliente_nombre"]); ?></strong></td>
                            <td class="correo-pedido"><?php echo escapar($pedido["cliente_email"]); ?></td>
                            <td><span class="estado-pedido" data-estado="<?php echo escapar($pedido["estado"]); ?>"><?php echo escapar(ucfirst($pedido["estado"])); ?></span></td>
                            <td class="importe-pedido">$<?php echo escapar(number_format((float)$pedido["total"], 2, ",", ".")); ?></td>
                            <td class="envio-pedido">
                                <?php if (!empty($pedido["envio_direccion"])): ?>
                                    <address>
                                        <strong><?php echo escapar($pedido["envio_nombre"]); ?></strong>
                                        <span><?php echo escapar($pedido["envio_direccion"]); ?></span>
                                        <span><?php echo escapar($pedido["envio_ciudad"]); ?>, <?php echo escapar($pedido["envio_departamento"]); ?></span>
                                        <span>Teléfono: <?php echo escapar(formatearTelefono($pedido["envio_telefono"])); ?></span>
                                    </address>
                                <?php else: ?>
                                    <span>Sin datos de envío registrados.</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="index.php?pagina=admin_pedido&amp;id=<?php echo escapar($pedido["id_pedido"]); ?>">Ver detalle</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
