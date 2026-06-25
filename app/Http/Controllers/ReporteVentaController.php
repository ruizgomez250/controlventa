<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\NumberToWords;
use App\Models\Caja;
use App\Models\User;
use App\Models\Venta;
use DateTime;
use TCPDF;

class ReporteVentaController extends Controller
{
    function pdffechasusuario($fechadesde, $fechahasta, $idusuario = null)
    {
        if (!auth()->user()->can('cajareporte editar')) {
            return redirect()->route('sinpermiso');
        }
        $cajas = '';
        $user = '';

        if (is_null($idusuario)) {
            $cajas = Caja::where('fecha_cobro', '>=', $fechadesde)
                ->where('fecha_cobro', '<=', $fechahasta)
                ->get();
        } else {
            $cajas = Caja::where('fecha_cobro', '>=', $fechadesde)
                ->where('fecha_cobro', '<=', $fechahasta)
                ->where('id_usuario', '=', $idusuario)
                ->get();
            $user = User::find($idusuario);
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Establecer márgenes y salto de página automático
        $pdf->SetMargins(1, 10, 1);
        $pdf->SetAutoPageBreak(true, 10);

        // Establecer fuente
        $pdf->SetFont('helvetica', 'B', 9);

        // Añadir página
        $pdf->AddPage();
        $pdf->SetCreator('easyStock');

        // Establecer título del documento
        $pdf->SetTitle('Reporte de Caja');
        $pdf->SetY(10); // Mover el cursor a la posición vertical 10mm
        $pdf->Cell(0, 10, 'Reporte de Caja', 0, 1, 'C'); // Celda centrada con el título

        // Crear tabla con títulos
        $pdf->SetFillColor(1, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        // Dibujar las celdas con un color de fondo más suave
        $pdf->Cell(20, 10, 'Número', 1, 0, 'C', true); // El último parámetro true indica que se debe aplicar el color de fondo
        $pdf->Cell(49, 10, 'Fecha de Cobro', 1, 0, 'C', true);
        $pdf->Cell(59, 10, 'Monto Gs.', 1, 0, 'C', true);
        $pdf->Cell(80, 10, 'Cajero', 1, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0); // RGB: negro
        if ($cajas->isEmpty()) {
            $pdf->Cell(0, 10, 'No hay datos disponibles', 1, 1, 'C');
        } else {
            $cont = 0;
            $total = 0;
            foreach ($cajas as $caja) {

                if (is_null($idusuario)) {
                    $user = User::find($caja->id_usuario);
                }
                $cont++;
                $fecha = new DateTime($caja->fecha_cobro);
                // Formatear la fecha en el formato deseado
                $fechaFormateada = $fecha->format('d/m/Y');
                $pdf->Cell(20, 10, $cont, 1, 0, 'C');
                $pdf->Cell(49, 10, $fechaFormateada, 1, 0, 'C');
                $pdf->Cell(59, 10, number_format($caja->monto, 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(80, 10, $user->name, 1, 1, 'C');
                $total = $total + $caja->monto;
            }
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(69, 10, 'TOTAL', 0, 0, 'C');
            $pdf->Cell(59, 10, number_format($total, 0, ',', '.') . ' Gs.', 0, 1, 'C');
            $formatter = new NumberToWords();
            $pdf->Cell(200, 10, '( ' . $formatter->toWords($total, 0) . ' )', 0, 0, 'C');
        }

        $pdf->Output('cajareporte.pdf', 'I');
        exit;
    }
     function generarReporte($fechadesde, $fechahasta, $idusuario = null)
    {

        // Consultar ventas
        if (is_null($idusuario)) {
            $ventas = Venta::with(['usuario', 'cliente'])
                ->where('fecha_emision', '>=', $fechadesde)
                ->where('fecha_emision', '<=', $fechahasta)
                ->orderBy('fecha_emision', 'desc')
                ->get();
            $usuarioNombre = 'TODOS LOS USUARIOS';
        } else {
            $ventas = Venta::with(['usuario', 'cliente'])
                ->where('fecha_emision', '>=', $fechadesde)
                ->where('fecha_emision', '<=', $fechahasta)
                ->where('id_usuario', '=', $idusuario)
                ->orderBy('fecha_emision', 'desc')
                ->get();
            $user = User::find($idusuario);
            $usuarioNombre = $user ? $user->name : 'N/A';
        }

        // Separar por estado: 1 = Vendido, 2 = Cobrado
        $ventasVendido = $ventas->where('estado', 1);
        $ventasCobrado = $ventas->where('estado', 2);

        $totalVendido = $ventasVendido->sum('total');
        $totalCobrado = $ventasCobrado->sum('total');
        $totalGeneral = $ventas->sum('total');

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->AddPage();
        $pdf->SetCreator('easyStock');
        $pdf->SetTitle('Reporte de Ventas');

        $moneda = \App\Models\Configuracion::where('descripcion', 'moneda')->value('observacion') ?? 'Gs.';

        // Título
        $pdf->SetY(10);
        $pdf->Cell(0, 10, __('SALES REPORT'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 6, __('Period') . ': ' . date('d/m/Y', strtotime($fechadesde)) . ' ' . __('to') . ' ' . date('d/m/Y', strtotime($fechahasta)), 0, 1, 'C');
        $pdf->Cell(0, 6, __('User') . ': ' . $usuarioNombre, 0, 1, 'C');
        $pdf->Ln(5);

        // ========== SOLD SALES (STATUS 1) ==========
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(255, 193, 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, __('SOLD SALES') . ' (Estado 1)', 0, 1, 'L', true);

        if ($ventasVendido->count() > 0) {
            $this->generarTablaVentas($pdf, $ventasVendido, __('Sold'), $moneda);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(250, 8, __('TOTAL SOLD SALES') . ':', 1, 0, 'R', true);
            $pdf->Cell(40, 8, number_format($totalVendido, 0, ',', '.') . ' ' . $moneda, 1, 1, 'R', true);
        } else {
            $pdf->Cell(0, 8, __('No sales with "Sold" status in this period.'), 1, 1, 'C');
        }

        $pdf->Ln(5);

        // ========== COLLECTED SALES (STATUS 2) ==========
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(40, 167, 69);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, __('COLLECTED SALES') . ' (Estado 2)', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);

        if ($ventasCobrado->count() > 0) {
            $this->generarTablaVentas($pdf, $ventasCobrado, __('Collected'), $moneda);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(250, 8, __('TOTAL COLLECTED SALES') . ':', 1, 0, 'R', true);
            $pdf->Cell(40, 8, number_format($totalCobrado, 0, ',', '.') . ' ' . $moneda, 1, 1, 'R', true);
        } else {
            $pdf->Cell(0, 8, __('No sales with "Collected" status in this period.'), 1, 1, 'C');
        }

        $pdf->Ln(5);

        // ========== GENERAL SUMMARY ==========
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(52, 58, 64);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, __('GENERAL SUMMARY'), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(100, 7, __('Total Sold Sales') . ':', 0, 0, 'L');
        $pdf->Cell(50, 7, number_format($totalVendido, 0, ',', '.') . ' ' . $moneda, 0, 1, 'L');

        $pdf->Cell(100, 7, __('Total Collected Sales') . ':', 0, 0, 'L');
        $pdf->Cell(50, 7, number_format($totalCobrado, 0, ',', '.') . ' ' . $moneda, 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(100, 8, __('GRAND TOTAL') . ':', 0, 0, 'L');
        $pdf->Cell(50, 8, number_format($totalGeneral, 0, ',', '.') . ' ' . $moneda, 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(100, 7, __('Quantity Sold') . ':', 0, 0, 'L');
        $pdf->Cell(50, 7, $ventasVendido->count() . ' ' . __('sales'), 0, 1, 'L');

        $pdf->Cell(100, 7, __('Quantity Collected') . ':', 0, 0, 'L');
        $pdf->Cell(50, 7, $ventasCobrado->count() . ' ' . __('sales'), 0, 1, 'L');

        $pdf->Cell(100, 7, __('Total Transactions') . ':', 0, 0, 'L');
        $pdf->Cell(50, 7, $ventas->count() . ' ' . __('sales'), 0, 1, 'L');

        // Total en letras
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, '( ' .$totalGeneral . ' )', 0, 1, 'C');

        $pdf->Output('reporteventas.pdf', 'I');
        exit;
    }

    private function generarTablaVentas($pdf, $ventas, $estadoTexto, $moneda)
    {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0, 0, 0);

        // Cabecera
        $pdf->Cell(15, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(30, 8, __('Date'), 1, 0, 'C', true);
        $pdf->Cell(50, 8, __('Client'), 1, 0, 'C', true);
        $pdf->Cell(45, 8, __('User'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, __('Voucher Type'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, __('Invoice No.'), 1, 0, 'C', true);
        $pdf->Cell(40, 8, __('Total') . ' (' . $moneda . ')', 1, 0, 'C', true);
        $pdf->Cell(35, 8, __('Status'), 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $cont = 0;

        foreach ($ventas as $venta) {
            $cont++;
            $fechaFormateada = date('d/m/Y', strtotime($venta->fecha_emision));
            $nombreCliente = $venta->cliente ? $venta->cliente->nombre : __('Final Consumer');
            $nombreUsuario = $venta->usuario ? $venta->usuario->name : 'N/A';

            $pdf->Cell(15, 7, $cont, 1, 0, 'C');
            $pdf->Cell(30, 7, $fechaFormateada, 1, 0, 'C');
            $pdf->Cell(50, 7, substr($nombreCliente, 0, 25), 1, 0, 'L');
            $pdf->Cell(45, 7, substr($nombreUsuario, 0, 20), 1, 0, 'L');
            $pdf->Cell(35, 7, $venta->tipo_comprobante ?? 'N/A', 1, 0, 'C');
            $pdf->Cell(35, 7, $venta->numero_factura ?? 'N/A', 1, 0, 'C');
            $pdf->Cell(40, 7, number_format($venta->total, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell(35, 7, $estadoTexto, 1, 1, 'C');
        }
    }
    
}
