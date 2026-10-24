<?php
$titulo = $activo === 1 ? "Reactivar producto" : "Dar de baja producto";
$tituloPagina = "Punto Moda - " . $titulo;
$seccionActiva = "gestion_productos";
$estiloExtra = "css/admin.css";
require __DIR__ . "/../header.php";
?>
<main>
    <h1 class="titulo-seccion"><?php echo escapar($titulo); ?></h1>
    <p>Producto: <strong><?php echo escapar($producto["nombre"]); ?></strong>.</p>
    <p><?php echo $activo === 1 ? "El producto volverá a mostrarse en el catálogo." : "El producto dejará de mostrarse en el catálogo. Sus datos y pedidos anteriores se conservarán."; ?></p>
    <form action="index.php?pagina=admin_producto_estado&id=<?php echo (int)$producto["id_producto"]; ?>" method="post" class="acciones-admin">
        <input type="hidden" name="activo" value="<?php echo (int)$activo; ?>">
        <button type="submit" class="btn"><?php echo $activo === 1 ? "Confirmar reactivación" : "Confirmar baja"; ?></button>
        <a href="index.php?pagina=admin_productos" class="btn btn-secundario">Cancelar</a>
    </form>
</main>
<?php require __DIR__ . "/../footer.php"; ?>
