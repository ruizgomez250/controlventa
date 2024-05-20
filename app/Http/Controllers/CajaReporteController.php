<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\User;
use App\Services\PermisoService;
use App\Helpers\NumberToWords;
use DateTime;
use Illuminate\Http\Request;
use TCPDF;

use function PHPUnit\Framework\isNull;

class CajaReporteController extends Controller
{
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function create()
    {
        





        $tienePermiso = $this->permisoService->verificarPermiso('CajaReporte', 'crear');
        if ($tienePermiso) {
            $usuarios = User::all();
            return view('cajareporte.create', compact('usuarios'));
        } else {
            return view('sinpermiso.index');
        }
    }
    function pdffechasusuario($fechadesde, $fechahasta, $idusuario = null)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('CajaReporte', 'editar');
        if ($tienePermiso) {
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
        } else {
            return redirect()->route('sinpermiso');
        }
    }
}
