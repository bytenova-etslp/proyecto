<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($tituloPagina); ?></title>
	<link rel="stylesheet" href="css/style.css?v=17c">
	<link rel="shortcut icon" type="image/png" href="imagenes/favicon.png">
    <?php if (isset($estiloExtra)): ?>
        <link rel="stylesheet" href="<?php echo escapar($estiloExtra); ?>?v=17c">
    <?php endif; ?>
</head>
<body>
	<header>
		<nav class="barra-navegacion">
			<a href="index.php" class="logo">Punto <span>Moda</span></a>
			<ul class="enlaces-navegacion">
				<li><a href="index.php" <?php if ($seccionActiva === "inicio") echo 'class="activo"'; ?>>Inicio</a></li>
				<li><a href="index.php?pagina=productos" <?php if ($seccionActiva === "productos") echo 'class="activo"'; ?>>Productos</a></li>
				<li><a href="index.php?pagina=carrito" <?php if ($seccionActiva === "carrito") echo 'class="activo"'; ?>>Carrito <span id="contador-carrito" class="contador-carrito">0</span></a></li>
                <?php if (usuarioActualRol() === "cliente"): ?>
                    <li><a href="index.php?pagina=mis_pedidos" <?php if ($seccionActiva === "mis_pedidos") echo 'class="activo"'; ?>>Mis pedidos</a></li>
                <?php endif; ?>
				<?php if (puedeGestionarProductos()): ?>
					<li><a href="index.php?pagina=admin" <?php if (in_array($seccionActiva, ["admin", "gestion_productos", "gestion_pedidos", "gestion_usuarios", "informe"], true)) echo 'class="activo"'; ?>>Administración</a></li>
				<?php endif; ?>
				<?php if (estaLogueado()): ?>
					<li><span>Hola, <?php echo escapar(usuarioActualNombre()); ?></span></li>
					<li>
						<form action="index.php?pagina=logout" method="post">
							<button type="submit" class="btn btn-secundario">Cerrar sesión</button>
						</form>
					</li>
				<?php else: ?>
					<li><a href="index.php?pagina=login" <?php if ($seccionActiva === "login") echo 'class="activo"'; ?>>Ingresar</a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</header>
