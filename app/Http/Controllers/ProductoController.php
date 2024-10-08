<?php

namespace App\Http\Controllers;


use App\Models\Producto;
use App\Models\Opcion;
use App\Models\TablaPorcentaje;
use App\Services\PermisoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

use TCPDF;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function index(): view
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'leer');
        if ($tienePermiso) {
            //obtenemos los datos
            $producto = Producto::All()->sortBy("descripcion");
            //asignar cabecera datatable
            $heads = [
                'Código', 'Descripción', 'Categoría', 'Stock', 'P. Costo', 'P. Venta', 'Impuesto', 'Estado', 'Acción'
            ];
            return view('productos.index', ['producto' =>  $producto, 'heads' => $heads]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'crear');
        if ($tienePermiso) {
            $headcat = ['Descripción', 'Acción'];
            $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
            $medida = Opcion::where('id_dominio', 5)->orderBy('descripcion')->get();
            return view('productos.create', ['medida' => $medida, 'categoria' => $categoria, 'headcat' => $headcat]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'crear');
        if ($tienePermiso) {
            try {
                // Obtener el valor actual del parámetro "estado" de la solicitud
                $estado = $request->input('estado', null);

                // Verificar si el parámetro "estado" existe en la solicitud
                if ($estado !== null) {
                    // Convertir el valor a 1 si es true y a 0 si es false
                    $estado = $estado ? "1" : "0";
                } else {
                    // Si el parámetro "estado" no existe en la solicitud, crearlo con valor 0
                    $estado = "0";
                }

                // Actualizar el valor del parámetro "estado" en la solicitud
                $request->merge(['estado' => $estado]);
                $request->validate(['descripcion' => 'required']);

                // Crear el producto
                Producto::create($request->all());

                // Redirigir con mensaje de éxito
                return redirect()->route('producto.index')->with('success', 'Producto creado exitosamente');
            } catch (Exception $e) {
                // Capturar excepciones y redirigir con mensaje de error
                return redirect()->back()->with('error', 'Error al crear el producto: ' . $e->getMessage());
            }
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'editar');
        if ($tienePermiso) {
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

            //$url = route('cargardetalleventa', $producto->codigo); // Genera la URL con el ID como parámetro
            $url = 'http://' . $host . '/controlventa/public/cargardetalleventa/' . $producto->codigo;
            // // Genera el código QR con la URL generada
            $qrCode = FacadesQrCode::size(100)->generate($url);



            // $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
            // $medida = Opcion::where('id_dominio', 5)->orderBy('descripcion')->get();
            // dd($producto);
            $codigo = $producto->codigo;

            // Genera el código QR con solo el código
            //$qrCode = FacadesQrCode::size(300)->generate($codigo);

            $categoria = Opcion::where('id_dominio', 3)->orderBy('descripcion')->get();
            $medida = Opcion::where('id_dominio', 5)->orderBy('descripcion')->get();
            return view('productos.edit', ['categoria' => $categoria, 'producto' => $producto, 'medida' => $medida, 'qrCode' => $qrCode]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'editar');
        if ($tienePermiso) {
            $estado = $request->input('estado', null);
            //
            if ($estado !== null) {
                // Convertir el valor a 1 si es true y a 0 si es false
                $estado = $estado ? "1" : "0";
            } else {
                // Si el parámetro "estado" no existe en la solicitud, crearlo con valor 0
                $estado = "0";
            }

            // Actualizar el valor del parámetro "estado" en la solicitud
            $request->merge(['estado' => $estado]);
            $producto->update($request->all());
            return redirect()->route('producto.index');
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'borrar');
        if ($tienePermiso) {
            if (!$producto) {
                // Maneja el caso en que el producto no existe
                return redirect()->back()->with('error', 'Producto no encontrado.');
            }

            // Intenta eliminar el producto y maneja restricciones de clave foránea
            try {
                $producto->delete();
                return redirect()->route('producto.index')->with('success', 'Producto eliminado con éxito.');
            } catch (\Illuminate\Database\QueryException $e) {
                // Maneja una excepción que indica restricciones de clave foránea
                return redirect()->back()->with('error', 'No se puede eliminar el producto debido a restricciones de clave foránea.');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }
    public function verifcod(Request $request)
    {

        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'leer');
        if ($tienePermiso) {
            try {
                // Obtén el código enviado por AJAX
                $codigo = $request->input('codigo');
                $cantpago = $request->input('cantpago');

                // Busca el producto en la base de datos por el código
                $producto = Producto::with('unidaddemedida')->where('codigo', $codigo)->first();

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
                    return response()->json($producto);
                } else {
                    // Si no se encuentra, devuelve un array vacío
                    return response()->json([]);
                }
            } catch (\Exception $e) {
                // Maneja la excepción, puedes registrarla, mostrar un mensaje de error o realizar alguna otra acción necesaria
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }
    public function createReporte()
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'leer');
        if ($tienePermiso) {
            $productos = Producto::where('estado', 1)->get();
            return view('productos.qr', compact('productos'));
        } else {
            return view('sinpermiso.index');
        }
    }
    public function qrproducto(int $id)
{
    $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'leer');
    if ($tienePermiso) {

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
        $url = 'http://' . $host . '/controlventa/public/cargardetalleventa/' . $producto->codigo;

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
        $pdf->Cell(0, 6, 'Codigo QR '.$producto->descripcion, 0, 1, 'C');
        
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
    } else {
        return redirect()->route('sinpermiso');
    }
}
}
