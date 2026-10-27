<?php
$tituloPagina = "Punto Moda - Carrito";
$seccionActiva = "carrito";
$estiloExtra = "css/pedidos.css";
require __DIR__ . "/header.php";
?>
<main>
		<section class="contenedor-carrito">
			<h2 class="titulo-seccion">Tu Carrito</h2>

			<!-- Se muestra solamente cuando no hay productos guardados -->
			<p id="carrito-vacio" class="carrito-vacio">
				Tu carrito está vacío. Agregá productos desde el catálogo.
			</p>

			<!-- JavaScript inserta aca los productos guardados -->
			<div id="carrito-container" class="carrito-lista"></div>

			<!-- Resumen del carrito y acciones principales -->
			<div class="carrito-resumen">
				<h3 id="total-carrito" class="total-carrito">Total: $0</h3>
                <p>El importe definitivo se calcula con los precios vigentes al confirmar.</p>
                <?php if (!estaLogueado()): ?>
                    <p><a href="index.php?pagina=login">Ingresa con tu cuenta de cliente</a> para confirmar. Tu carrito se conserva.</p>
                <?php elseif (usuarioActualRol() !== "cliente"): ?>
                    <p>Para realizar un pedido debes ingresar con una cuenta de cliente.</p>
                <?php endif; ?>
				<div class="carrito-acciones">
					<button id="btn-vaciar" class="btn btn-secundario">Vaciar carrito</button>
					<button id="btn-finalizar" class="btn" <?php if (usuarioActualRol() !== "cliente") echo 'disabled'; ?>>Continuar compra</button>
				</div>
			</div>
            <p id="mensaje-pedido" role="status" aria-live="polite"></p>
		</section>
	</main>
<?php
$scriptExtra = "js/carrito.js";
require __DIR__ . "/footer.php";
?>
