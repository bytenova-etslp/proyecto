<?php
$tituloPagina = "Punto Moda - Login";
$seccionActiva = "login";
require __DIR__ . "/header.php";
?>
<main>
		<!-- Formulario de inicio de sesion. La conexion con PHP se agregara mas adelante. -->
		<div class="contenedor-autenticacion">
			<div class="pestanas-autenticacion">
				<a href="index.php?pagina=login" class="pestana-autenticacion activo">Iniciar Sesión</a>
				<a href="index.php?pagina=registro" class="pestana-autenticacion">Registrarse</a>
			</div>

			<form id="form-login" action="#">
				<div class="grupo-formulario">
					<label for="login-email">Correo Electrónico</label>
					<input type="email" id="login-email" placeholder="ejemplo@correo.com" required>
				</div>
				<div class="grupo-formulario">
					<label for="login-password">Contraseña</label>
					<input type="password" id="login-password" placeholder="********" required>
				</div>
				<button type="submit" class="btn">Ingresar</button>
				<p id="mensaje-login" class="mensaje-formulario"></p>
				<div class="pie-autenticacion">
					<p>¿No tenés cuenta? <a href="index.php?pagina=registro">Registrate acá</a></p>
				</div>
			</form>
		</div>
	</main>
<?php
$scriptExtra = "js/login.js";
require __DIR__ . "/footer.php";
?>
