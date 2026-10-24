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

    public function buscarPorEmail($email) {
        $sql = "SELECT id_usuario, nombre, email, contrasena, rol, activo
                FROM Usuario WHERE email = ? LIMIT 1";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_bind_param($consulta, "s", $email);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $usuario = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($consulta);
        return $usuario;
    }

    public function obtenerPorId($idUsuario) {
        $sql = "SELECT id_usuario, nombre, rol, activo
                FROM Usuario WHERE id_usuario = ?";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idUsuario);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $usuario = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($consulta);
        return $usuario;
    }
}
