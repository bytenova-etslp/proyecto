<?php
require_once __DIR__ . "/../models/PagoModel.php";

class InformeController {
    private $modelo;

    public function __construct($conexion) {
        requerirRol(["empleado", "administrador"]);
        $this->modelo = new PagoModel($conexion);
    }

    public function mostrar() {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            header("Allow: GET");
            http_response_code(405);
            $mensajeError = "Método no permitido.";
            require __DIR__ . "/../views/error.php";
            return;
        }
        $hoy = new DateTimeImmutable("now", new DateTimeZone("America/Montevideo"));
        $desde = $_GET["desde"] ?? $hoy->format("Y-m-01");
        $hasta = $_GET["hasta"] ?? $hoy->format("Y-m-d");
        $errores = [];
        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            $errores[] = "Ingresá fechas válidas con formato AAAA-MM-DD.";
        } elseif ($desde > $hasta) {
            $errores[] = "La fecha desde no puede ser posterior a la fecha hasta.";
        }
        // Los campos siempre reciben texto, aun si se manipula la URL con arreglos.
        $desde = is_string($desde) ? $desde : "";
        $hasta = is_string($hasta) ? $hasta : "";
        $numero = $_GET["numero"] ?? "1";
        $numero = is_string($numero) ? filter_var($numero, FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1, "max_range" => 2147483647]]) : false;
        if ($numero === false) $errores[] = "La página solicitada no es válida.";

        $resumen = ["intentos" => 0, "aprobados" => 0, "rechazados" => 0, "total_cobrado" => 0];
        $pagos = [];
        $promedio = 0;
        $paginas = 1;
        $urlAnterior = $urlSiguiente = "";
        if (empty($errores)) {
            $inicio = $desde . " 00:00:00";
            $fin = (new DateTimeImmutable($hasta))->modify("+1 day")->format("Y-m-d") . " 00:00:00";
            $resumen = $this->modelo->resumirPeriodo($inicio, $fin);
            $limite = 50;
            $paginas = max(1, (int)ceil((int)$resumen["intentos"] / $limite));
            if ($numero > $paginas) {
                http_response_code(404);
                $mensajeError = "Página del informe no encontrada.";
                require __DIR__ . "/../views/error.php";
                return;
            }
            $pagos = $this->modelo->listarPeriodo($inicio, $fin, $limite, ($numero - 1) * $limite);
            $promedio = (int)$resumen["aprobados"] > 0
                ? (float)$resumen["total_cobrado"] / (int)$resumen["aprobados"] : 0;
            $base = "index.php?pagina=admin_informe&desde=" . $desde . "&hasta=" . $hasta . "&numero=";
            if ($numero > 1) $urlAnterior = $base . ($numero - 1);
            if ($numero < $paginas) $urlSiguiente = $base . ($numero + 1);
        } else {
            http_response_code(400);
        }
        require __DIR__ . "/../views/admin/informe.php";
    }

    private function fechaValida($valor) {
        if (!is_string($valor) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $valor)
            || $valor < "1000-01-01" || $valor > "9998-12-31") return false;
        $fecha = DateTimeImmutable::createFromFormat("!Y-m-d", $valor);
        return $fecha && $fecha->format("Y-m-d") === $valor;
    }
}
