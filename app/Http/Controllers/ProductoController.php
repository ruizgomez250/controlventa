<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Impuesto;
use App\Models\Producto;
use App\Models\ProductoPrecioTier;
use App\Models\Opcion;
use App\Models\TablaPorcentaje;
use App\Models\TemporalVentaDetalle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class ProductoController extends Controller
{
    public function index(): View
    {
        if (!auth()->user()->can('producto leer')) {
            return view('sinpermiso.index');
        }
            $producto = Producto::with([
                'impuesto',
                'unidaddemedida',
                'categoriaproducto',
                'precioTiers'
            ])
                ->orderBy('id', 'desc')
                ->get();

            $heads = [
                'N°',
                'Unidad M.',
                'Descripción',
                'Categoría',
                'Stock',
                'P. Costo',
                'P. Venta',
                'Impuesto',
                'Estado',
                'Tramos Mayorista',
                'Acción'
            ];
            return view('productos.index', compact('producto', 'heads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('producto crear')) {
            return view('sinpermiso.index');
        }
        $headcat = ['Descripción', 'Acción'];
        $impuestos = Impuesto::all();
        $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
        $medida = Opcion::where('id_dominio', 5)->orderBy('id')->get();
        return view('productos.create', ['medida' => $medida, 'categoria' => $categoria, 'impuestos' => $impuestos, 'headcat' => $headcat]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('producto crear')) {
            return view('sinpermiso.index');
        }
        try {
            $estado = $request->input('estado', null);
            $estado = $estado !== null ? ($estado ? "1" : "0") : "0";
            $request->merge(['estado' => $estado]);
            $request->validate([
                'descripcion'   => 'required|string|max:255',
                'id_categoria'  => 'required|exists:opciones,id',
                'id_medida'     => 'required|exists:opciones,id',
                'id_impuesto'   => 'required|exists:impuestos,id',
            ]);
            $producto = Producto::create($request->all());

            if ($request->has('tier_cantidad')) {
                foreach ($request->tier_cantidad as $i => $cantidad) {
                    if (isset($request->tier_precio[$i]) && $cantidad > 0) {
                        ProductoPrecioTier::create([
                            'id_producto' => $producto->id,
                            'cantidad_desde' => $cantidad,
                            'precio_unitario' => $request->tier_precio[$i],
                            'orden' => $i,
                        ]);
                    }
                }
            }

            return redirect()->route('producto.index')->with('success', 'Producto creado exitosamente');
        } catch (Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }








    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        if (!auth()->user()->can('producto editar')) {
            return view('sinpermiso.index');
        }
            $iniFilePath = public_path('config.ini');

            // Verificar si el archivo existe antes de intentar leerlo
            $host = 0;
            if (file_exists($iniFilePath)) {
                // Lee el archivo config.ini y carga su contenido en un array estructurado
                $config = parse_ini_file($iniFilePath, true);

                // Accede al valor del host dentro de la sección database
                $host = $config['database']['host'];
                // Haz algo con el valor obtenido, como pasarlo a una vista

            }

            $url = $producto->codigo; // Genera la URL con el ID como parámetro
            //$url = 'http://' . $host . '/controlventa/public/cargardetalleventa/' . $producto->codigo;
            // // Genera el código QR con la URL generada
            //$url ='https://concurso.diputados.gov.py/documentos/FORMULARIO_DE_P_20240615_214330.pdf';
            $qrCode = FacadesQrCode::size(100)->generate($url);



            // $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
            // $medida = Opcion::where('id_dominio', 5)->orderBy('descripcion')->get();
            // dd($producto);
            $codigo = $producto->codigo;

            // Genera el código QR con solo el código
            //$qrCode = FacadesQrCode::size(300)->generate($codigo);
            $headcat = ['Descripción', 'Acción'];
            $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
            $medida = Opcion::where('id_dominio', 5)->orderBy('descripcion')->get();
            $impuestos = Impuesto::all();
            $producto->load('precioTiers');
            return view('productos.edit', ['headcat' => $headcat, 'categoria' => $categoria, 'producto' => $producto, 'medida' => $medida, 'qrCode' => $qrCode, 'impuestos' => $impuestos]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        if (!auth()->user()->can('producto editar')) {
            return redirect()->route('sinpermiso');
        }

        $estado = $request->input('estado', null);
        $estado = $estado !== null ? ($estado ? "1" : "0") : "0";
        $request->merge(['estado' => $estado]);

        $validated = $request->validate([
            'descripcion'   => 'required|string|max:255',
            'id_categoria'  => 'required|exists:opciones,id',
            'id_medida'     => 'required|exists:opciones,id',
            'id_impuesto'   => 'required|exists:impuestos,id',
            'imagen'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $path = $request->file('imagen')->store('productos', 'public');
            $validated['imagen'] = $path;
        }

        $producto->update($validated);

        $producto->precioTiers()->delete();
        if ($request->has('tier_cantidad')) {
            foreach ($request->tier_cantidad as $i => $cantidad) {
                if (isset($request->tier_precio[$i]) && $cantidad > 0) {
                    ProductoPrecioTier::create([
                        'id_producto' => $producto->id,
                        'cantidad_desde' => $cantidad,
                        'precio_unitario' => $request->tier_precio[$i],
                        'orden' => $i,
                    ]);
                }
            }
        }

        return redirect()->route('producto.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        if (!auth()->user()->can('producto borrar')) {
            return redirect()->route('sinpermiso');
        }

        try {
            // ✅ eliminar imagen si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $producto->delete();
            return redirect()->route('producto.index')->with('success', 'Producto eliminado con éxito.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'No se puede eliminar el producto debido a restricciones de clave foránea.');
        }
    }
    public function verifcod(Request $request)
    {
        if (!auth()->user()->can('producto leer')) {
            return redirect()->route('sinpermiso');
        }
        try {
            $codigo = $request->input('codigo');
            $cantpago = $request->input('cantpago');
            $producto = Producto::with(['unidaddemedida', 'precioTiers'])->where('codigo', $codigo)->first();
            $configuracion = Configuracion::firstOrNew(
                ['descripcion' => 'condicionv'],
                ['estado' => 0]
            );
            if (!$configuracion->exists) {
                $configuracion->save();
            }
            if ($producto) {
                if ($cantpago > 1) {
                    $tablaPorcentaje = TablaPorcentaje::where('cuota', $cantpago)
                        ->where('estado', 1)
                        ->first();
                    if ($tablaPorcentaje) {
                        $precioVentaContado = $producto->pventa;
                        $porcentajeRecargo = $tablaPorcentaje->porcentaje;
                        $precio = $precioVentaContado * (1 + $porcentajeRecargo / 100);
                        $producto->pventa = $precio;
                    }
                }
                return response()->json([
                    'producto' => $producto,
                    'configuracion' => $configuracion
                ]);
            } else {
                return response()->json([]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function desdetemporal(Request $request)
    {
        if (!auth()->user()->can('producto leer')) {
            return redirect()->route('sinpermiso');
        }
            try {
                // Obtén el código enviado por AJAX
                $cantpago = $request->input('cantpago');
                $idUsuarioLogueado = auth()->user()->id;
                $temporales = TemporalVentaDetalle::with('producto') // Cargar la relación con los productos
                    ->where('user_id', $idUsuarioLogueado)
                    ->first();
                if ($temporales) {
                    $producto = Producto::with(['unidaddemedida', 'precioTiers'])->where('id', $temporales->producto_id)->first();
                    $temporales->delete();
                    //dd($producto);
                    $configuracion = Configuracion::firstOrNew(
                        ['descripcion' => 'condicionv'], // Condiciones de búsqueda
                        ['estado' => 0] // Valores por defecto si no se encuentra
                    );

                    // Si la configuración fue creada (no encontrada en la base de datos), la guardamos
                    if (!$configuracion->exists) {
                        $configuracion->save();
                    }
                    // Verifica si se encontró un producto
                    if ($producto) {
                        if ($cantpago > 1) {
                            $tablaPorcentaje = TablaPorcentaje::where('cuota', $cantpago)
                                ->where('estado', 1)
                                ->first();

                            // Verificar si se encontró el registro
                            if ($tablaPorcentaje) {
                                $precioVentaContado = $producto->pventa;
                                $porcentajeRecargo = $tablaPorcentaje->porcentaje;
                                $precio = $precioVentaContado * (1 + $porcentajeRecargo / 100);
                                $producto->pventa = $precio;
                            }
                        }
                        // Devuelve los datos del producto en formato JSON
                        return response()->json([
                            'producto' => $producto,
                            'configuracion' => $configuracion
                        ]);
                    } else {
                        // Si no se encuentra, devuelve un array vacío
                        return response()->json([]);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
    }
    public function createReporte()
    {
        if (!auth()->user()->can('producto leer')) {
            return view('sinpermiso.index');
        }
        $productos = Producto::where('estado', 1)->get();
        return view('productos.qr', compact('productos'));
    }
    public function qrproducto(int $id)
    {
        if (!auth()->user()->can('producto leer')) {
            return redirect()->route('sinpermiso');
        }

            $iniFilePath = public_path('config.ini');

            // Verificar si el archivo existe antes de intentar leerlo
            $host = 0;
            if (file_exists($iniFilePath)) {
                // Lee el archivo config.ini y carga su contenido en un array estructurado
                $config = parse_ini_file($iniFilePath, true);

                // Accede al valor del host dentro de la sección database
                $host = $config['database']['host'];
            }

            $producto = Producto::findOrFail($id);
            //$url = 'http://' . $host . '/controlventa/public/cargardetalleventa/' . $producto->codigo;
            $url = $producto->codigo;
            // Crear el código QR
            $qrCode = QrCode::create($url);

            // Crear el escritor de código QR usando GD
            $writer = new PngWriter();

            // Generar el contenido del código QR
            $result = $writer->write($qrCode);

            // Guardar el código QR como un archivo PNG
            $imagePath = public_path('qrcode.png');
            $result->saveToFile($imagePath);

            // Verifica que el archivo se haya generado correctamente
            if (!file_exists($imagePath)) {
                throw new Exception("No se pudo generar el código QR en formato PNG.");
            }

            // Crea el PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Establecer márgenes y salto de página automático
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false, 10);

            // Añadir página
            $pdf->AddPage();

            // Establecer fuente
            $pdf->SetFont('helvetica', 'B', 12);

            // Establecer título del documento
            $pdf->SetTitle('QR');

            // Añadir título centrado
            $pdf->Cell(0, 6, 'Codigo QR ' . $producto->descripcion, 0, 1, 'C');

            // Establecer fuente más pequeña para el contenido
            $pdf->SetFont('helvetica', '', 12);

            // Ajustar la posición inicial para los códigos QR después del título
            $qrSize = 30; // Tamaño del código QR en mm
            $x = 10; // Posición inicial en el eje X
            $y = 16; // Posición inicial en el eje Y, dejando espacio para el título

            // Bucle para repetir la imagen del código QR en todo el PDF
            while ($y < 280) { // mientras no se exceda la altura de la página
                while ($x < 190) { // mientras no se exceda el ancho de la página
                    // Insertar el código QR en el PDF
                    $pdf->Image($imagePath, $x, $y, $qrSize, $qrSize, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);

                    // Actualizar la posición en el eje X para la próxima imagen
                    $x += $qrSize + 10; // Aumentar la posición en el eje X y agregar un espacio adicional
                }
                // Reiniciar la posición en el eje X y actualizar la posición en el eje Y para la próxima fila
                $x = 10; // Restablecer la posición en el eje X
                $y += $qrSize + 10; // Mover a la siguiente fila
            }

            // Salida del PDF
            $pdf->Output('producto_qr.pdf', 'I');
            exit;
    }


    public function indexl()
    {
        $productos = Producto::with(['categoriaproducto', 'unidaddemedida'])
            ->select('id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'stock', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'impuesto', 'imagen')
            ->get()
            ->map(function ($producto) {
                $producto->stock = (int) $producto->stock;
                $producto->pcosto = (int) round($producto->pcosto);
                $producto->pventa = (int) round($producto->pventa);
                $producto->impuesto = (int) round($producto->impuesto);

                return $producto;
            });

        return response()->json($productos);
    }

    public function showl($id)
    {
        $producto = Producto::with(['categoriaproducto', 'unidaddemedida'])
            ->select('id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'stock', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'impuesto')
            ->findOrFail($id);

        return response()->json($producto);
    }

    public function barcodeproducto(int $id)
    {
        if (!auth()->user()->can('producto leer')) {
            return redirect()->route('sinpermiso');
        }

            $producto = Producto::findOrFail($id);

            $codigo = $producto->codigo;

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false, 10);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'Codigo de Barras ' . $producto->descripcion, 0, 1, 'C');

            $pdf->SetFont('helvetica', '', 10);

            $style = [
                'position' => '',
                'align' => 'C',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => [0, 0, 0],
                'bgcolor' => false,
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 8,
                'stretchtext' => 4
            ];

            $barcodeWidth = 50;
            $barcodeHeight = 20;

            $x = 10;
            $y = 20;

            while ($y < 280) {
                while ($x < 190) {
                    $pdf->write1DBarcode($codigo, 'C128', $x, $y, $barcodeWidth, $barcodeHeight, 0.4, $style, 'N');
                    $x += $barcodeWidth + 10;
                }

                $x = 10;
                $y += $barcodeHeight + 15;
            }

            $pdf->Output('producto_barcode.pdf', 'I');
            exit;
    }
}
