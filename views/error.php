<?php
$tituloPagina = "Punto Moda - Aviso";
$seccionActiva = "";
require __DIR__ . "/header.php";
?>
	<main>
		<p><?php echo htmlspecialchars($mensajeError); ?></p>
		<a href="index.php" class="btn">Volver al inicio</a>
	</main>
<?php require __DIR__ . "/footer.php"; ?>
