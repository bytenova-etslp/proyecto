<?php
$tituloPagina = "Punto Moda - Inicio";
$seccionActiva = "inicio";
require __DIR__ . "/header.php";
?>
<main>
		<!-- Presentacion principal del sitio -->
		<section class="presentacion">
			<h1>Bienvenidos a Punto Moda</h1>
			<p>La revolución del e-commerce de indumentaria. Diseños exclusivos y calidad garantizada.</p>
			<a href="index.php?pagina=productos" class="btn">Ver Catálogo</a>
		</section>

		<!-- El producto destacado se obtiene desde la base de datos -->
		<section>
			<h2 class="titulo-seccion">Producto Destacado</h2>
			<div class="cuadricula-productos">
				<?php if ($producto): ?>
					<div class="tarjeta-producto">
						<a href="index.php?pagina=producto&id=<?php echo $producto["id_producto"]; ?>" class="enlace-imagen-producto">
							<div class="imagen-producto">
								<img src="<?php echo htmlspecialchars($producto["imagen"]); ?>" alt="<?php echo htmlspecialchars($producto["nombre"]); ?>">
							</div>
						</a>
						<div class="info-producto">
							<div class="categoria-producto"><?php echo htmlspecialchars($producto["categoria"]); ?></div>
							<h3 class="titulo-producto"><?php echo htmlspecialchars($producto["nombre"]); ?></h3>

							<div class="precio-producto">$ <?php echo escapar(number_format((float)$producto["precio"], 2, ",", ".")); ?></div>

							<div class="botones-producto">
								<a href="index.php?pagina=producto&id=<?php echo $producto["id_producto"]; ?>" class="btn btn-secundario">Ver detalle</a>
							</div>
						</div>
					</div>
				<?php else: ?>
					<p>No hay productos cargados en este momento.</p>
				<?php endif; ?>
			</div>
		</section>
	</main>
<?php
require __DIR__ . "/footer.php";
?>
