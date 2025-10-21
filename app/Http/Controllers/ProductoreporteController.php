<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\User;
use App\services\PermisoService;
use App\Helpers\NumberToWords;
use App\Models\Producto;
use App\Models\Venta;
use DateTime;
use Illuminate\Http\Request;
use TCPDF;

use function PHPUnit\Framework\isNull;

class ProductoreporteController extends Controller
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
            $productos = Producto::all();
            return view('reportes.ganancia', compact('productos'));
        } else {
            return view('sinpermiso.index');
        }
    }
    public function pdfganancia($fechadesde, $fechahasta, $idproducto = null)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Reporte', 'editar');
        if ($tienePermiso) {
            // Validar y formatear las fechas
            $fechadesde = date('Y-m-d', strtotime($fechadesde));
            $fechahasta = date('Y-m-d', strtotime($fechahasta));

            // Construir la consulta
            $query = Venta::whereBetween('fecha_emision', [$fechadesde, $fechahasta]);

            // Obtener las ventas con sus detalles y productos
            $ventas = $query->with(['detalles' => function ($q) use ($idproducto) {
                if ($idproducto) {
                    $q->where('id_producto', $idproducto);
                }
            }, 'detalles.producto'])->get();

            // Calcular los totales y margen de ganancia
            $totalIngresos = 0;
            $totalCostos = 0;

            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    $ingresos = $detalle->precio_u * $detalle->cantidad;
                    $costos = $producto->pcosto * $detalle->cantidad;

                    $totalIngresos += $ingresos;
                    $totalCostos += $costos;
                }
            }

            $gananciaBruta = $totalIngresos - $totalCostos;
            $margenGanancia = $totalIngresos ? ($gananciaBruta / $totalIngresos) * 100 : 0;

            // Generar el PDF
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
            $pdf->SetTitle('Reporte de Ganancia');
            $pdf->SetY(10); // Mover el cursor a la posición vertical 10mm
            $pdf->Cell(0, 10, 'Reporte de Ganancia', 0, 1, 'C'); // Celda centrada con el título

            // Mostrar la fórmula y el margen de ganancia
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 10, 'Fórmula: Margen de Ganancia = (Beneficios Brutos / Ingresos) * 100', 0, 1, 'C');
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Margen de Ganancia: ' . number_format($margenGanancia, 2) . '%', 0, 1, 'C');

            // Crear tabla con títulos
            $pdf->SetFillColor(1, 0, 0);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(20, 10, 'Número', 1, 0, 'C', true); // El último parámetro true indica que se debe aplicar el color de fondo
            $pdf->Cell(49, 10, 'Fecha de Emisión', 1, 0, 'C', true);
            $pdf->Cell(59, 10, 'Total Venta Gs.', 1, 0, 'C', true);
            $pdf->Cell(80, 10, 'Detalles', 1, 1, 'C', true);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0); // RGB: negro

            if ($ventas->isEmpty()) {
                $pdf->Cell(0, 10, 'No hay datos disponibles', 1, 1, 'C');
            } else {
                $cont = 0;
                foreach ($ventas as $venta) {
                    $cont++;
                    $fecha = new DateTime($venta->fecha_emision);
                    $fechaFormateada = $fecha->format('d/m/Y');
                    $pdf->Cell(20, 10, $cont, 1, 0, 'C');
                    $pdf->Cell(49, 10, $fechaFormateada, 1, 0, 'C');
                    $pdf->Cell(59, 10, number_format($venta->total, 0, ',', '.'), 1, 0, 'C');

                    $detalleStr = '';
                    foreach ($venta->detalles as $detalle) {
                        $detalleStr .= $detalle->producto->descripcion . ' - Cantidad: ' . $detalle->cantidad . ' - Precio Unitario: ' . number_format($detalle->precio_u, 0, ',', '.') . ' Gs.\n';
                    }
                    $pdf->MultiCell(80, 10, $detalleStr, 1, 'C', 0, 1, '', '', true);
                }
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(69, 10, 'TOTAL', 0, 0, 'C');
                $pdf->Cell(59, 10, number_format($totalIngresos, 0, ',', '.') . ' Gs.', 0, 1, 'C');
                $formatter = new NumberToWords();
                $pdf->Cell(200, 10, '( ' . $formatter->toWords($totalIngresos, 0) . ' )', 0, 0, 'C');
            }

            $pdf->Output('gananciareporte.pdf', 'I');
            exit;
        } else {
            return redirect()->route('sinpermiso');
        }
    }
}
