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

    // El panel nunca consulta contrasenas ni hashes.
    public function listarTodosAdmin() {
        $sql = "SELECT u.id_usuario, u.nombre, u.email, u.rol, u.activo,
                       COUNT(p.id_pedido) AS cantidad_pedidos
                FROM Usuario u
                LEFT JOIN Pedido p ON p.id_usuario = u.id_usuario
                GROUP BY u.id_usuario, u.nombre, u.email, u.rol, u.activo
                ORDER BY u.activo DESC, u.nombre ASC, u.id_usuario ASC";
        $consulta = mysqli_prepare($this->conexion, $sql);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $usuarios = [];
        while ($usuario = mysqli_fetch_assoc($resultado)) {
            $usuarios[] = $usuario;
        }
        mysqli_stmt_close($consulta);
        return $usuarios;
    }

    public function cambiarRol($idUsuario, $rol) {
        $consulta = mysqli_prepare($this->conexion, "UPDATE Usuario SET rol = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($consulta, "si", $rol, $idUsuario);
        $guardado = mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);
        return $guardado;
    }

    public function cambiarActivo($idUsuario, $activo) {
        $consulta = mysqli_prepare($this->conexion, "UPDATE Usuario SET activo = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($consulta, "ii", $activo, $idUsuario);
        $guardado = mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);
        return $guardado;
    }
}
