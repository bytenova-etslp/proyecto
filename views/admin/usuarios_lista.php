<?php
$tituloPagina = "Punto Moda - Gestión de usuarios";
$seccionActiva = "gestion_usuarios";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main class="usuarios-gestion">
    <h1 class="titulo-seccion">Gestión de usuarios</h1>
    <div class="acciones-admin">
        <a href="index.php?pagina=admin" class="btn btn-secundario">Volver al panel</a>
    </div>
    <p>Administrá los permisos y el acceso de las cuentas registradas.</p>
    <?php if ($aviso !== null): ?>
        <p class="aviso-admin aviso-<?php echo escapar($aviso["tipo"]); ?>" role="status"><?php echo escapar($aviso["texto"]); ?></p>
    <?php endif; ?>

    <?php if (empty($usuarios)): ?>
        <p>Todavía no hay usuarios registrados.</p>
    <?php else: ?>
        <div class="contenedor-tabla">
            <table class="tabla">
                <caption>Usuarios activos y desactivados</caption>
                <thead>
                    <tr><th scope="col">ID</th><th scope="col">Nombre</th><th scope="col">Correo electrónico</th><th scope="col">Rol</th><th scope="col">Pedidos</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php $esMiCuenta = (int)$usuario["id_usuario"] === $idActual; ?>
                        <tr class="<?php echo (int)$usuario["activo"] === 1 ? "" : "usuario-inactivo"; ?>">
                            <td><?php echo escapar($usuario["id_usuario"]); ?></td>
                            <td>
                                <?php echo escapar($usuario["nombre"]); ?>
                                <?php if ($esMiCuenta): ?><small class="cuenta-propia">Tu cuenta</small><?php endif; ?>
                            </td>
                            <td class="correo-usuario"><?php echo escapar($usuario["email"]); ?></td>
                            <td><?php echo escapar(ucfirst($usuario["rol"])); ?></td>
                            <td><?php echo escapar($usuario["cantidad_pedidos"]); ?></td>
                            <td><span class="estado-usuario"><?php echo (int)$usuario["activo"] === 1 ? "Activo" : "Desactivado"; ?></span></td>
                            <td>
                                <?php if ($esMiCuenta): ?>
                                    <span class="nota-cuenta-propia">No podés modificar tu propio rol ni desactivar tu cuenta.</span>
                                <?php else: ?>
                                    <div class="acciones-usuario">
                                        <form action="index.php?pagina=admin_usuario_rol&amp;id=<?php echo escapar($usuario["id_usuario"]); ?>" method="post" class="form-rol-usuario">
                                            <input type="hidden" name="token" value="<?php echo escapar($token); ?>">
                                            <label for="rol-<?php echo escapar($usuario["id_usuario"]); ?>">Rol de <?php echo escapar($usuario["nombre"]); ?></label>
                                            <select id="rol-<?php echo escapar($usuario["id_usuario"]); ?>" name="rol" required>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?php echo escapar($rol); ?>" <?php if ($usuario["rol"] === $rol) echo "selected"; ?>><?php echo escapar(ucfirst($rol)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn">Guardar rol</button>
                                        </form>
                                        <form action="index.php?pagina=admin_usuario_estado&amp;id=<?php echo escapar($usuario["id_usuario"]); ?>" method="post">
                                            <input type="hidden" name="token" value="<?php echo escapar($token); ?>">
                                            <input type="hidden" name="activo" value="<?php echo (int)$usuario["activo"] === 1 ? "0" : "1"; ?>">
                                            <button type="submit" class="btn btn-secundario" aria-label="<?php echo escapar(((int)$usuario["activo"] === 1 ? "Desactivar" : "Reactivar") . " cuenta de " . $usuario["nombre"]); ?>"><?php echo (int)$usuario["activo"] === 1 ? "Desactivar cuenta" : "Reactivar cuenta"; ?></button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
