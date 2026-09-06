<?php
require_once "php/conexion.php";

$sql = "SELECT Producto.id_producto, Producto.nombre, Producto.precio, Producto.precio_promocional,
		Producto.imagen, Producto.stock, Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		ORDER BY Producto.id_producto ASC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Punto Moda - Productos</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" type="image/png" href="imagenes/favicon.png">
</head>
<body>

	<!--
		Archivo: productos.php
		Proyecto: Punto Moda - ByteNova
	-->

	<!-- Encabezado y navegacion principal -->
	<header>
		<nav class="barra-navegacion">
			<a href="index.php" class="logo">Punto <span>Moda</span></a>
			<ul class="enlaces-navegacion">
				<li><a href="index.php">Inicio</a></li>
				<li><a href="productos.php" class="activo">Productos</a></li>
				<li><a href="carrito.html">Carrito <span id="contador-carrito" class="contador-carrito">0</span></a></li>
				<li><a href="login.html">Ingresar</a></li>
			</ul>
		</nav>
	</header>

	<main>
		<section>
			<h2 class="titulo-seccion">Nuestro Catálogo</h2>
			<p class="texto-introductorio">Explorá nuestras prendas exclusivas.</p>

			<!-- PHP genera una tarjeta por cada producto de la base de datos -->
			<div id="catalogo-container" class="cuadricula-productos">
				<?php if (mysqli_num_rows($resultado) > 0): ?>

					<?php while ($producto = mysqli_fetch_assoc($resultado)): ?>
						<?php
							$precioCarrito = $producto["precio"];
							if ($producto["precio_promocional"] != null) {
								$precioCarrito = $producto["precio_promocional"];
							}

							$sqlTalles = "SELECT talle FROM Producto_Talle WHERE id_producto = " . $producto["id_producto"];
							$resultadoTalles = mysqli_query($conexion, $sqlTalles);
						?>

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
										<?php while ($filaTalle = mysqli_fetch_assoc($resultadoTalles)): ?>
											<option value="<?php echo htmlspecialchars($filaTalle["talle"]); ?>"><?php echo htmlspecialchars($filaTalle["talle"]); ?></option>
										<?php endwhile; ?>
									</select>
								</div>

								<div class="botones-producto">
									<a href="detalle-producto.php?id=<?php echo $producto["id_producto"]; ?>" class="btn btn-secundario">Ver detalle</a>
									<button
										type="button"
										class="btn btn-agregar-producto"
										data-id="<?php echo $producto["id_producto"]; ?>"
										data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
										data-precio="<?php echo $precioCarrito; ?>"
										<?php if ($producto["stock"] == 0) echo "disabled"; ?>
									>Agregar al Carrito</button>
								</div>
							</div>
						</div>

					<?php endwhile; ?>

				<?php else: ?>
					<p>No hay productos cargados en este momento.</p>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<!-- Mensaje que JavaScript muestra al agregar un producto -->
	<div id="mensaje-flotante" class="mensaje-flotante"></div>

	<footer>
		<p>&copy; Diseñado por ByteNova 2026</p>
	</footer>

	<!-- storage.js se carga primero porque catalogo.js usa sus funciones -->
	<script src="js/storage.js"></script>
	<script src="js/catalogo.js"></script>
</body>
</html>
