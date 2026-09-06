<?php
require_once "php/conexion.php";

$sql = "SELECT Producto.id_producto, Producto.nombre, Producto.precio, Producto.precio_promocional,
		Producto.imagen, Categoria.nombre AS categoria
		FROM Producto
		JOIN Categoria ON Producto.id_categoria = Categoria.id_categoria
		WHERE Producto.id_producto = 1";
$resultado = mysqli_query($conexion, $sql);
$producto = mysqli_fetch_assoc($resultado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Punto Moda - Inicio</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" type="image/png" href="imagenes/favicon.png">
</head>
<body>

	<!--
		Archivo: index.php
		Proyecto: Punto Moda - ByteNova
	-->

	<!-- Encabezado y navegacion principal -->
	<header>
		<nav class="barra-navegacion">
			<a href="index.php" class="logo">Punto <span>Moda</span></a>
			<ul class="enlaces-navegacion">
				<li><a href="index.php" class="activo">Inicio</a></li>
				<li><a href="productos.php">Productos</a></li>
				<li><a href="carrito.html">Carrito <span id="contador-carrito" class="contador-carrito">0</span></a></li>
				<li><a href="login.html">Ingresar</a></li>
			</ul>
		</nav>
	</header>

	<main>
		<!-- Presentacion principal del sitio -->
		<section class="presentacion">
			<h1>Bienvenidos a Punto Moda</h1>
			<p>La revolución del e-commerce de indumentaria. Diseños exclusivos y calidad garantizada.</p>
			<a href="productos.php" class="btn">Ver Catálogo</a>
		</section>

		<!-- El producto destacado se obtiene desde la base de datos -->
		<section>
			<h2 class="titulo-seccion">Producto Destacado</h2>
			<div class="cuadricula-productos">
				<?php if ($producto): ?>
					<div class="tarjeta-producto">
						<a href="detalle-producto.php?id=<?php echo $producto["id_producto"]; ?>" class="enlace-imagen-producto">
							<div class="imagen-producto">
								<img src="<?php echo htmlspecialchars($producto["imagen"]); ?>" alt="<?php echo htmlspecialchars($producto["nombre"]); ?>">
							</div>
						</a>
						<div class="info-producto">
							<div class="categoria-producto"><?php echo htmlspecialchars($producto["categoria"]); ?></div>
							<h3 class="titulo-producto"><?php echo htmlspecialchars($producto["nombre"]); ?></h3>

							<?php if ($producto["precio_promocional"] != null): ?>
								<div class="precio-producto">$ <?php echo number_format($producto["precio_promocional"], 0, ",", "."); ?></div>
							<?php else: ?>
								<div class="precio-producto">$ <?php echo number_format($producto["precio"], 0, ",", "."); ?></div>
							<?php endif; ?>

							<div class="botones-producto">
								<a href="detalle-producto.php?id=<?php echo $producto["id_producto"]; ?>" class="btn btn-secundario">Ver detalle</a>
							</div>
						</div>
					</div>
				<?php else: ?>
					<p>No hay productos cargados en este momento.</p>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<footer>
		<p>&copy; Diseñado por ByteNova 2026</p>
	</footer>

	<!-- Actualiza el contador del carrito guardado en LocalStorage -->
	<script src="js/storage.js"></script>
</body>
</html>
