<?php
require_once "php/conexion.php";

$id = (int)$_GET["id"];

$sql = "SELECT Producto.id_producto, Producto.nombre, Producto.descripcion, Producto.precio,
		Producto.precio_promocional, Producto.imagen, Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		WHERE Producto.id_producto = $id";
$resultado = mysqli_query($conexion, $sql);
$producto = mysqli_fetch_assoc($resultado);

if ($producto) {
	$precioCarrito = $producto["precio"];
	if ($producto["precio_promocional"] != null) {
		$precioCarrito = $producto["precio_promocional"];
	}

	$sqlTalles = "SELECT talle FROM Producto_Talle WHERE id_producto = $id";
	$resultadoTalles = mysqli_query($conexion, $sqlTalles);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Punto Moda - Detalle de producto</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" type="image/png" href="imagenes/favicon.png">
</head>
<body>

	<!--
		Archivo: detalle-producto.php
		Proyecto: Punto Moda - ByteNova
		Ultima actualizacion: 05/09/2026
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
							<?php while ($filaTalle = mysqli_fetch_assoc($resultadoTalles)): ?>
								<option value="<?php echo htmlspecialchars($filaTalle["talle"]); ?>"><?php echo htmlspecialchars($filaTalle["talle"]); ?></option>
							<?php endwhile; ?>
						</select>
					</div>

					<div class="detalle-acciones">
						<button
							type="button"
							class="btn btn-agregar-producto"
							data-id="<?php echo $producto["id_producto"]; ?>"
							data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
							data-precio="<?php echo $precioCarrito; ?>"
						>Agregar al Carrito</button>
						<a href="productos.php" class="btn btn-secundario">Volver al catálogo</a>
					</div>
				</div>
			</section>
		<?php else: ?>
			<p>No se encontró el producto.</p>
		<?php endif; ?>
	</main>

	<!-- Mensaje que JavaScript muestra al agregar el producto -->
	<div id="mensaje-flotante" class="mensaje-flotante"></div>

	<footer>
		<p>&copy; Diseñado por ByteNova 2026</p>
	</footer>

	<!-- storage.js se carga primero porque catalogo.js usa sus funciones -->
	<script src="js/storage.js"></script>
	<script src="js/catalogo.js"></script>
</body>
</html>
