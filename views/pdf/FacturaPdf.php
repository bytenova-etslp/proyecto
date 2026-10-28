<?php
require_once __DIR__ . "/../../lib/fpdf/fpdf.php";

// Presentacion PDF: recibe datos ya autorizados y calculados, sin consultar SQL.
class FacturaPdf extends FPDF {
    private $numeroFactura = "";

    public static function generar($factura) {
        $pdf = new self("P", "mm", "A4");
        $pdf->numeroFactura = $factura["numero"];
        $pdf->SetMargins(14, 14, 14);
        $pdf->SetAutoPageBreak(true, 22);
        $pdf->AliasNbPages();
        $pdf->SetTitle("Factura " . $factura["numero"] . " - Punto Moda", true);
        $pdf->SetAuthor("Punto Moda");
        $pdf->AddPage();
        $pdf->contenido($factura);
        return $pdf->Output("S");
    }

    public function Header() {
        $this->SetFont("Helvetica", "B", 21);
        $this->SetTextColor(233, 69, 96);
        $this->Cell(110, 9, "Punto Moda");
        $this->SetTextColor(26, 26, 46);
        $this->SetFont("Helvetica", "B", 16);
        $this->Cell(72, 9, "FACTURA", 0, 1, "R");
        $this->SetFont("Helvetica", "", 10);
        $this->Cell(110, 7, "Indumentaria");
        $this->Cell(72, 7, $this->texto("N.º " . $this->numeroFactura), 0, 1, "R");
        $this->SetDrawColor(233, 69, 96);
        $this->SetLineWidth(0.7);
        $this->Line(14, 34, 196, 34);
        $this->SetY(41);
    }

    public function Footer() {
        $this->SetY(-16);
        $this->SetFont("Helvetica", "", 8);
        $this->SetTextColor(98, 103, 118);
        $this->Cell(125, 5, $this->texto("Punto Moda | Factura " . $this->numeroFactura));
        $this->Cell(57, 5, $this->texto("Página " . $this->PageNo() . " de {nb}"), 0, 0, "R");
    }

    private function contenido($factura) {
        $pedido = $factura["pedido"];
        $pago = $factura["pago"];
        $this->SetFont("Helvetica", "B", 10);
        $this->SetTextColor(34, 99, 59);
        $this->Cell(0, 7, $this->texto("Pago aprobado - Gracias por tu compra"), 0, 1);
        $this->SetTextColor(26, 26, 46);
        $this->SetFont("Helvetica", "", 9);
        $this->Cell(0, 6, $this->texto("Pedido #" . $pedido["id_pedido"] . " | Fecha del pedido: " . $pedido["fecha"]), 0, 1);
        $this->Cell(0, 6, $this->texto("Fecha de pago: " . $pago["fecha"] . " | Tarjeta **** " . $pago["ultimos4"]), 0, 1);
        $this->Ln(7);

        $y = $this->GetY();
        $this->SetFont("Helvetica", "B", 11);
        $this->Cell(94, 7, "Cliente");
        $this->Cell(88, 7, $this->texto("Datos de envío"), 0, 1);
        $this->SetFont("Helvetica", "", 10);
        $cliente = $pedido["cliente_nombre"] . "\n" . $pedido["cliente_email"];
        if (!empty($pedido["envio_telefono"])) $cliente .= "\nTeléfono: " . formatearTelefono($pedido["envio_telefono"]);
        $this->MultiCell(88, 5.5, $this->texto($cliente), 0, "L");
        $finCliente = $this->GetY();
        $this->SetXY(108, $y + 7);
        $envio = !empty($pedido["envio_direccion"])
            ? $pedido["envio_nombre"] . "\n" . $pedido["envio_direccion"] . "\n" . $pedido["envio_ciudad"] . ", " . $pedido["envio_departamento"]
            : "Sin datos de envío registrados.";
        $this->MultiCell(88, 5.5, $this->texto($envio), 0, "L");
        $this->SetY(max($finCliente, $this->GetY()) + 9);
        $this->cabeceraTabla();
        foreach ($factura["detalle"] as $linea) {
            $this->fila([$linea["nombre"], $linea["talle"], (string)$linea["cantidad"],
                "$ " . number_format($linea["precio_centavos"] / 100, 2, ",", "."),
                "$ " . number_format($linea["subtotal_centavos"] / 100, 2, ",", ".")]);
        }
        if ($this->GetY() + 38 > 273) $this->AddPage();
        $this->Ln(7);
        $this->SetFont("Helvetica", "B", 13);
        $this->Cell(132, 11, "Total pagado", 0, 0, "R");
        $this->Cell(50, 11, "$ " . number_format($factura["total_centavos"] / 100, 2, ",", "."), 0, 1, "R");
        $this->Ln(4);
        $this->SetFont("Helvetica", "", 9);
        $this->SetTextColor(98, 103, 118);
        $this->MultiCell(0, 5, $this->texto("Importes expresados en pesos uruguayos (UYU).\nConservá este documento como constancia de tu compra."), 0, "L");
    }

    private function cabeceraTabla() {
        $this->SetFont("Helvetica", "B", 9);
        $this->SetFillColor(240, 242, 246);
        $this->SetTextColor(26, 26, 46);
        foreach ([[90, "Producto"], [16, "Talle"], [16, "Cant."], [29, "P. unitario"], [31, "Subtotal"]] as $columna) {
            $this->Cell($columna[0], 9, $columna[1], 0, 0, "L", true);
        }
        $this->Ln();
    }

    private function fila($valores) {
        $anchos = [90, 16, 16, 29, 31];
        $this->SetFont("Helvetica", "", 9);
        $lineas = [];
        foreach ($valores as $i => $valor) $lineas[] = $this->envolver($this->texto($valor), $anchos[$i] - 3);
        $alto = max(array_map("count", $lineas)) * 5 + 5;
        if ($this->GetY() + $alto > 273) {
            $this->AddPage();
            $this->cabeceraTabla();
            $this->SetFont("Helvetica", "", 9);
        }
        $x = 14;
        $y = $this->GetY();
        foreach ($lineas as $i => $renglones) {
            foreach ($renglones as $n => $renglon) {
                $this->SetXY($x + 1.5, $y + 2.5 + $n * 5);
                $this->Cell($anchos[$i] - 3, 5, $renglon, 0, 0, $i >= 2 ? "R" : "L");
            }
            $x += $anchos[$i];
        }
        $this->SetDrawColor(221, 225, 232);
        $this->SetLineWidth(0.2);
        $this->Line(14, $y + $alto, 196, $y + $alto);
        $this->SetXY(14, $y + $alto);
    }

    // Corta tambien palabras largas para mantener cada celda dentro de su columna.
    private function envolver($texto, $ancho) {
        $texto = str_replace(["\r", "\n", "\t"], " ", $texto);
        $lineas = [];
        $actual = "";
        foreach (str_split($texto) as $letra) {
            if ($actual !== "" && $this->GetStringWidth($actual . $letra) > $ancho) {
                $corte = strrpos($actual, " ");
                if ($corte !== false && $corte > 0) {
                    $lineas[] = substr($actual, 0, $corte);
                    $actual = ltrim(substr($actual, $corte + 1) . $letra);
                } else {
                    $lineas[] = $actual;
                    $actual = $letra;
                }
            } else $actual .= $letra;
        }
        $lineas[] = $actual;
        return $lineas;
    }

    private function texto($valor) {
        // Las fuentes estandar de FPDF usan Windows-1252 (incluye tildes y ene).
        $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', "", (string)$valor);
        return mb_convert_encoding($valor ?? "", "Windows-1252", "UTF-8");
    }
}
