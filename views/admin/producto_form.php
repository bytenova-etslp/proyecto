<?php
$tituloPagina = "Punto Moda - " . $titulo;
$seccionActiva = "gestion_productos";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main>
    <h1 class="titulo-seccion"><?php echo escapar($titulo); ?></h1>
    <p><a href="index.php?pagina=admin_productos">Volver a la lista</a></p>
    <?php if (!empty($errores)): ?>
        <ul class="errores-admin" role="alert">
            <?php foreach ($errores as $error): ?>
                <li><?php echo escapar($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?php echo escapar($urlAccion); ?>" method="post" enctype="multipart/form-data" class="form-admin">
        <div class="grupo-formulario">
            <label for="producto-nombre">Nombre</label>
            <input type="text" id="producto-nombre" name="nombre" maxlength="100" value="<?php echo escapar($producto["nombre"]); ?>" required>
        </div>
        <div class="grupo-formulario">
            <label for="producto-descripcion">Descripción</label>
            <textarea id="producto-descripcion" name="descripcion" rows="4" maxlength="500"><?php echo escapar($producto["descripcion"]); ?></textarea>
        </div>
        <div class="grupo-formulario">
            <label for="producto-categoria">Categoría</label>
            <select id="producto-categoria" name="id_categoria" required>
                <option value="">Seleccioná una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo (int)$categoria["id_categoria"]; ?>" <?php if ((string)$producto["id_categoria"] === (string)$categoria["id_categoria"]) echo "selected"; ?>><?php echo escapar($categoria["nombre"]); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="producto-precio">Precio ($)</label>
            <input type="number" id="producto-precio" name="precio" min="0" max="99999999.99" step="0.01" value="<?php echo escapar($producto["precio"]); ?>" required>
        </div>
        <div class="grupo-formulario">
            <label for="producto-minimo">Stock mínimo del producto</label>
            <input type="number" id="producto-minimo" name="stock_minimo" min="0" max="2147483647" step="1" value="<?php echo escapar($producto["stock_minimo"]); ?>" required>
            <small>La alerta aparece si la suma de unidades de todos los talles queda por debajo de este mínimo.</small>
        </div>

        <fieldset>
            <legend>Stock por talle</legend>
            <p>Los talles existentes se conservan. Indicá 0 unidades cuando un talle esté agotado.</p>
            <div class="contenedor-tabla">
                <table class="tabla">
                    <thead><tr><th>Talle</th><th>Unidades</th><th>Acción</th></tr></thead>
                    <tbody id="filas-talles">
                        <?php foreach ($producto["talles"] as $fila): ?>
                            <?php $protegido = in_array($fila["talle"], $tallesProtegidos, true); ?>
                            <tr>
                                <td><input type="text" name="talles[]" maxlength="10" value="<?php echo escapar($fila["talle"]); ?>" aria-label="Talle" <?php if ($protegido) echo "readonly"; ?>></td>
                                <td><input type="number" name="stocks[]" min="0" max="2147483647" step="1" value="<?php echo escapar($fila["stock"]); ?>" aria-label="Unidades del talle"></td>
                                <td><?php if (!$protegido): ?><button type="button" class="btn btn-secundario btn-quitar-talle">Quitar fila</button><?php else: ?>Talle registrado<?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" id="btn-agregar-talle" class="btn btn-secundario">Agregar talle</button>
        </fieldset>

        <?php if (!empty($producto["imagen"])): ?>
            <p>Imagen actual:</p>
            <img src="<?php echo escapar($producto["imagen"]); ?>" alt="Imagen actual del producto" class="miniatura-grande">
        <?php endif; ?>
        <div class="grupo-formulario">
            <label for="producto-imagen">Imagen (JPG, PNG o WEBP, máximo 2 MB)</label>
            <input type="file" id="producto-imagen" name="imagen" accept="image/jpeg,image/png,image/webp">
            <small>Al editar, dejá este campo vacío para conservar la imagen actual.</small>
        </div>
        <div class="acciones-admin">
            <button type="submit" class="btn">Guardar producto</button>
            <a href="index.php?pagina=admin_productos" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</main>
<?php
$scriptExtra = "js/admin-productos.js";
require __DIR__ . "/../footer.php";
?>
