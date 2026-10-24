<?php
$tituloPagina = "Punto Moda - Carrito";
$seccionActiva = "carrito";
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
				<div class="carrito-acciones">
					<button id="btn-vaciar" class="btn btn-secundario">Vaciar carrito</button>
					<button id="btn-finalizar" class="btn">Finalizar compra</button>
				</div>
			</div>
		</section>
	</main>
<?php
$scriptExtra = "js/carrito.js";
require __DIR__ . "/footer.php";
?>
