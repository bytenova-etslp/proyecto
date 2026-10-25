<?php
$tituloPagina = "Punto Moda - Productos";
$seccionActiva = "productos";
$estiloExtra = "css/catalogo.css";
require __DIR__ . "/header.php";
?>
<main>
		<section>
			<h2 class="titulo-seccion">Nuestro Catálogo</h2>
			<p class="texto-introductorio">Explorá nuestras prendas exclusivas.</p>

            <form action="index.php" method="get" class="buscador">
                <input type="hidden" name="pagina" value="productos">
                <div class="campo-busqueda">
                    <label for="q">Buscar productos</label>
                    <input type="text" id="q" name="q" maxlength="50" placeholder="Ej.: campera" value="<?php echo escapar($busqueda); ?>">
                </div>
                <div class="campo-busqueda">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo (int)$categoria["id_categoria"]; ?>" <?php if ($idCategoriaSeleccionada === (int)$categoria["id_categoria"]) echo "selected"; ?>><?php echo escapar($categoria["nombre"]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Buscar</button>
                <a href="index.php?pagina=productos" class="btn btn-secundario">Ver todos</a>
            </form>
            <?php if (!empty($erroresFiltros)): ?>
                <ul class="errores-busqueda" role="alert">
                    <?php foreach ($erroresFiltros as $error): ?>
                        <li><?php echo escapar($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="resumen-busqueda" role="status">
                    <?php echo count($productos); ?> producto(s) encontrado(s).
                    <?php if ($busqueda !== ""): ?>
                        Búsqueda: <strong><?php echo escapar($busqueda); ?></strong>.
                        <a href="<?php echo escapar($urlSinBusqueda); ?>">Limpiar búsqueda</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

			<!-- PHP genera una tarjeta por cada producto de la base de datos -->
			<div id="catalogo-container" class="cuadricula-productos">
				<?php if (!empty($productos)): ?>

					<?php foreach ($productos as $producto): ?>
						

						<div class="tarjeta-producto">
							<div class="imagen-producto">
								<img src="<?php echo htmlspecialchars($producto["imagen"]); ?>" alt="<?php echo htmlspecialchars($producto["nombre"]); ?>">
							</div>
							<div class="info-producto">
								<div class="categoria-producto"><?php echo htmlspecialchars($producto["categoria"]); ?></div>
								<h3 class="titulo-producto"><?php echo htmlspecialchars($producto["nombre"]); ?></h3>

								<?php if ($producto["precio_promocional"] != null): ?>
									<div class="precio-producto">$ <?php echo number_format($producto["precio_promocional"], 0, ",", "."); ?></div>
								<?php else: ?>
									<div class="precio-producto">$ <?php echo number_format($producto["precio"], 0, ",", "."); ?></div>
								<?php endif; ?>

								<div class="seleccion-talle">
									<label for="talle-<?php echo $producto["id_producto"]; ?>">Talle</label>
									<select id="talle-<?php echo $producto["id_producto"]; ?>" class="selector-talle" data-producto-id="<?php echo $producto["id_producto"]; ?>">
										<?php foreach ($producto["talles"] as $filaTalle): ?>
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

								<div class="botones-producto">
									<a href="index.php?pagina=producto&id=<?php echo $producto["id_producto"]; ?>" class="btn btn-secundario">Ver detalle</a>
									<button
										type="button"
										class="btn btn-agregar-producto"
										data-id="<?php echo $producto["id_producto"]; ?>"
										data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
										data-precio="<?php echo $producto["precio_carrito"]; ?>"
										<?php if ($producto["stock"] <= 0) echo "disabled"; ?>
									>Agregar al Carrito</button>
								</div>
							</div>
						</div>

					<?php endforeach; ?>

				<?php else: ?>
					<p>No encontramos productos con esos criterios.</p>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<!-- Mensaje que JavaScript muestra al agregar un producto -->
	<div id="mensaje-flotante" class="mensaje-flotante"></div>
<?php
$scriptExtra = "js/catalogo.js";
require __DIR__ . "/footer.php";
?>
