<?php
$tituloPagina = "Punto Moda - Detalle de producto";
$seccionActiva = "productos";
require __DIR__ . "/header.php";
?>
<main>
		<?php if ($producto): ?>
			<!-- Los datos del producto seleccionado se obtienen desde la base de datos -->
			<section class="detalle-producto">
				<div class="detalle-imagen">
					<img src="<?php echo htmlspecialchars($producto["imagen"]); ?>" alt="<?php echo htmlspecialchars($producto["nombre"]); ?>">
				</div>

				<div class="detalle-info">
					<p class="categoria-producto"><?php echo htmlspecialchars($producto["categoria"]); ?></p>
					<h1><?php echo htmlspecialchars($producto["nombre"]); ?></h1>

					<?php if ($producto["precio_promocional"] != null): ?>
						<p class="detalle-precio">$ <?php echo number_format($producto["precio_promocional"], 0, ",", "."); ?></p>
					<?php else: ?>
						<p class="detalle-precio">$ <?php echo number_format($producto["precio"], 0, ",", "."); ?></p>
					<?php endif; ?>

					<p class="detalle-descripcion"><?php echo htmlspecialchars($producto["descripcion"]); ?></p>

					<div class="seleccion-talle detalle-talle">
						<label for="talle-detalle">Talle</label>
						<select id="talle-detalle" class="selector-talle" data-producto-id="<?php echo $producto["id_producto"]; ?>">
							<?php foreach ($talles as $filaTalle): ?>
								<option value="<?php echo htmlspecialchars($filaTalle["talle"]); ?>" data-stock="<?php echo (int)$filaTalle["stock"]; ?>" <?php if ($filaTalle["stock"] <= 0) echo "disabled"; ?>>
								    <?php echo htmlspecialchars($filaTalle["talle"]); ?>
								    <?php if ($filaTalle["stock"] > 0): ?>
								        (<?php echo $filaTalle["stock"]; ?> disponibles)
								    <?php else: ?>
								        (agotado)
								    <?php endif; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="detalle-acciones">
						<button
							type="button"
							class="btn btn-agregar-producto"
							data-id="<?php echo $producto["id_producto"]; ?>"
							data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
							data-precio="<?php echo $precioCarrito; ?>"
							<?php if ($producto["stock"] <= 0) echo "disabled"; ?>
						>Agregar al Carrito</button>
						<a href="index.php?pagina=productos" class="btn btn-secundario">Volver al catálogo</a>
					</div>
				</div>
			</section>
		<?php else: ?>
			<p>No se encontró el producto.</p>
		<?php endif; ?>
	</main>

	<!-- Mensaje que JavaScript muestra al agregar el producto -->
	<div id="mensaje-flotante" class="mensaje-flotante"></div>
<?php
$scriptExtra = "js/catalogo.js";
require __DIR__ . "/footer.php";
?>
