<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$basedatos = "puntomoda";

$conexion = mysqli_connect($servidor, $usuario, $contrasena, $basedatos);

if (!$conexion) {
	die("Error de conexión: " . mysqli_connect_error());
}
?>
