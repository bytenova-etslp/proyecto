<?php
const IMG_PRODUCTOS_DIR = __DIR__ . "/../imagenes/productos/";
const IMG_PRODUCTOS_URL = "imagenes/productos/";
const IMG_MAX_BYTES = 2 * 1024 * 1024;

function guardarImagenProducto($archivo) {
    if ($archivo === null) {
        return ["ok" => true, "nombre" => null];
    }
    if (!is_array($archivo) || !isset($archivo["error"]) || !is_int($archivo["error"])) {
        return ["ok" => false, "error" => "Los datos de la imagen no son validos."];
    }
    if ($archivo["error"] === UPLOAD_ERR_NO_FILE) {
        return ["ok" => true, "nombre" => null];
    }
    if ($archivo["error"] === UPLOAD_ERR_INI_SIZE || $archivo["error"] === UPLOAD_ERR_FORM_SIZE) {
        return ["ok" => false, "error" => "La imagen supera el maximo de 2 MB."];
    }
    if ($archivo["error"] !== UPLOAD_ERR_OK || !isset($archivo["tmp_name"]) || !is_string($archivo["tmp_name"])) {
        return ["ok" => false, "error" => "No se pudo subir la imagen. Intenta nuevamente."];
    }
    $temporal = $archivo["tmp_name"];
    if (!is_file($temporal) || filesize($temporal) > IMG_MAX_BYTES) {
        return ["ok" => false, "error" => "La imagen no es valida o supera el maximo de 2 MB."];
    }
    $info = @getimagesize($temporal);
    $permitidos = [IMAGETYPE_JPEG => "jpg", IMAGETYPE_PNG => "png", IMAGETYPE_WEBP => "webp"];
    if ($info === false || !isset($permitidos[$info[2]])) {
        return ["ok" => false, "error" => "Selecciona una imagen JPG, PNG o WEBP."];
    }
    if (!is_dir(IMG_PRODUCTOS_DIR) && !mkdir(IMG_PRODUCTOS_DIR, 0775, true)) {
        return ["ok" => false, "error" => "No se pudo crear la carpeta de imagenes en el servidor."];
    }
    $nombre = bin2hex(random_bytes(8)) . "." . $permitidos[$info[2]];
    if (!move_uploaded_file($temporal, IMG_PRODUCTOS_DIR . $nombre)) {
        return ["ok" => false, "error" => "No se pudo guardar la imagen. Revisa los permisos de imagenes/productos."];
    }
    return ["ok" => true, "nombre" => IMG_PRODUCTOS_URL . $nombre];
}

function borrarImagenProducto($ruta) {
    // Solo borra archivos generados por esta carga; conserva las imagenes originales.
    if (!is_string($ruta) || !preg_match('~^imagenes/productos/[a-f0-9]{16}\.(jpg|png|webp)$~', $ruta)) {
        return;
    }
    $archivo = IMG_PRODUCTOS_DIR . basename($ruta);
    if (is_file($archivo)) {
        unlink($archivo);
    }
}
