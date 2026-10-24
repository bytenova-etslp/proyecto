<?php
$tituloPagina = "Punto Moda - Registro";
$seccionActiva = "login";
require __DIR__ . "/header.php";
?>
<main>
		<!-- Formulario de registro conectado con PHP mediante JavaScript. -->
		<div class="contenedor-autenticacion">
			<div class="pestanas-autenticacion">
				<a href="index.php?pagina=login" class="pestana-autenticacion">Iniciar Sesión</a>
				<a href="index.php?pagina=registro" class="pestana-autenticacion activo">Registrarse</a>
			</div>

			<form id="form-registro" action="index.php?pagina=registro" method="post">
				<div class="grupo-formulario">
					<label for="registro-nombre">Nombre completo</label>
					<input type="text" id="registro-nombre" name="nombre" maxlength="100" autocomplete="name" placeholder="Nombre y apellido" required>
				</div>

				<div class="grupo-formulario">
					<label for="registro-email">Correo Electrónico</label>
					<input type="email" id="registro-email" name="email" maxlength="100" autocomplete="email" placeholder="ejemplo@correo.com" required>
				</div>

				<div class="grupo-formulario">
					<label for="registro-password">Contraseña (mínimo 8 caracteres)</label>
					<input type="password" id="registro-password" name="contrasena" minlength="8" autocomplete="new-password" placeholder="********" required>
				</div>

				<div class="grupo-formulario">
					<label for="registro-confirmar">Confirmar contraseña</label>
					<input type="password" id="registro-confirmar" name="confirmar" minlength="8" autocomplete="new-password" placeholder="********" required>
				</div>

				<button type="submit" class="btn">Crear cuenta</button>
				<p id="mensaje-registro" class="mensaje-formulario" aria-live="polite"></p>
				<div class="pie-autenticacion">
					<p>¿Ya tenés cuenta? <a href="index.php?pagina=login">Iniciá sesión acá</a></p>
				</div>
			</form>
		</div>
	</main>
<?php
$scriptExtra = "js/registro.js";
require __DIR__ . "/footer.php";
?>
