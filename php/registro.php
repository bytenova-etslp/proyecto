<?php
/*
    Proyecto: Punto Moda - ByteNova
    Ultima actualizacion: 22/10/2026
    Valida el registro y guarda una cuenta de cliente con contrasena hasheada.
*/
header("Content-Type: application/json; charset=utf-8");

// Devuelve un mensaje que JavaScript muestra junto al formulario.
function responder($correcto, $mensaje, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(["correcto" => $correcto, "mensaje" => $mensaje]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    responder(false, "El registro requiere enviar el formulario.", 405);
}

$nombre = $_POST["nombre"] ?? "";
$email = $_POST["email"] ?? "";
$contrasena = $_POST["contrasena"] ?? "";
$confirmar = $_POST["confirmar"] ?? "";

// Los datos tambien se validan en el servidor, aunque JavaScript ya los revise.
if (!is_string($nombre) || !is_string($email) || !is_string($contrasena) || !is_string($confirmar)) {
    responder(false, "Los datos del formulario no son validos.", 400);
}
$nombre = trim($nombre);
$email = trim($email);
if ($nombre === "" || $email === "" || $contrasena === "" || $confirmar === "") {
    responder(false, "Debe completar todos los campos.", 400);
}
if (preg_match('/^.{1,100}$/us', $nombre) !== 1) {
    responder(false, "El nombre debe tener entre 1 y 100 caracteres.", 400);
}
if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, "Ingrese un correo valido de hasta 100 caracteres.", 400);
}
if (preg_match('/^.{6,}$/us', $contrasena) !== 1) {
    responder(false, "La contrasena debe tener al menos 6 caracteres.", 400);
}
// Evita contrasenas que bcrypt pueda truncar o rechazar.
if (strlen($contrasena) > 72 || strpos($contrasena, "\0") !== false) {
    responder(false, "La contrasena es demasiado larga o contiene caracteres no admitidos.", 400);
}
if ($contrasena !== $confirmar) {
    responder(false, "Las contrasenas no coinciden.", 400);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    require_once __DIR__ . "/conexion.php";
    mysqli_set_charset($conexion, "utf8mb4");
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // El rol se fija en el servidor; los datos se envian con una consulta preparada.
    $sql = "INSERT INTO Usuario (nombre, email, contrasena, rol, activo)
            VALUES (?, ?, ?, 'cliente', 1)";
    $consulta = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($consulta, "sss", $nombre, $email, $hash);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_close($consulta);
    responder(true, "Cuenta creada correctamente.", 201);
} catch (mysqli_sql_exception $error) {
    // UNIQUE impide repetir correos, incluso si llegan dos registros a la vez.
    if ($error->getCode() == 1062) {
        responder(false, "Ese correo ya esta registrado.", 409);
    }
    responder(false, "No se pudo guardar la cuenta. Intenta nuevamente.", 500);
}
