<?php
// Consultas de usuarios. El modelo recibe la conexion y no genera HTML.
class UsuarioModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crear($nombre, $email, $hash) {
        $sql = "INSERT INTO Usuario (nombre, email, contrasena, rol, activo)
                VALUES (?, ?, ?, 'cliente', 1)";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_bind_param($consulta, "sss", $nombre, $email, $hash);
        $guardado = mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);
        return $guardado;
    }
}
