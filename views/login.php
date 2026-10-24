<?php
$tituloPagina = "Punto Moda - Login";
$seccionActiva = "login";
require __DIR__ . "/header.php";
?>
<main>
		<!-- PHP valida las credenciales y vuelve a mostrar los errores si es necesario. -->
		<div class="contenedor-autenticacion">
			<div class="pestanas-autenticacion">
				<a href="index.php?pagina=login" class="pestana-autenticacion activo">Iniciar Sesión</a>
				<a href="index.php?pagina=registro" class="pestana-autenticacion">Registrarse</a>
			</div>

            <?php if (($_GET["cuenta"] ?? "") === "desactivada"): ?>
                <p class="mensaje-formulario">Tu sesión terminó porque la cuenta ya no está habilitada.</p>
            <?php endif; ?>

			<form id="form-login" action="index.php?pagina=login" method="post">
				<div class="grupo-formulario">
					<label for="login-email">Correo Electrónico</label>
					<input type="email" id="login-email" name="email" maxlength="100" autocomplete="email" value="<?php echo escapar($email); ?>" placeholder="ejemplo@correo.com" required>
				</div>
				<div class="grupo-formulario">
					<label for="login-password">Contraseña</label>
					<input type="password" id="login-password" name="contrasena" autocomplete="current-password" placeholder="********" required>
				</div>
				<button type="submit" class="btn">Ingresar</button>
				<?php if (!empty($errores)): ?>
                    <ul id="mensaje-login" class="mensaje-formulario" aria-live="polite">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo escapar($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
				<div class="pie-autenticacion">
					<p>¿No tenés cuenta? <a href="index.php?pagina=registro">Registrate acá</a></p>
				</div>
			</form>
		</div>
	</main>
<?php
require __DIR__ . "/footer.php";
?>
