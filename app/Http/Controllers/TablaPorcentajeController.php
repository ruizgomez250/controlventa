<?php

namespace App\Http\Controllers;

use App\Models\TablaPorcentaje;
use App\Services\PermisoService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TablaPorcentajeController extends Controller
{
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function index(): View
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'leer');
        if ($tienePermiso) {
            //obtenemos los datos
            $tablaporc = TablaPorcentaje::All();
            //asignar cabecera datatable
            $heads = [
                'Código', 'Porcentaje', 'Cuota', 'Estado', 'Acción'
            ];
            return view('tablaporc.index', ['tablaporc' =>  $tablaporc, 'heads' => $heads]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'crear');
        if ($tienePermiso) {
            try {
                // Obtener el valor actual del parámetro "estado" de la solicitud
                $estado = 1;

                // Actualizar el valor del parámetro "estado" en la solicitud
                $request->merge(['estado' => $estado]);
                //dd($request->input());
                // Crear el producto
                TablaPorcentaje::create($request->all());

                // Redirigir con mensaje de éxito
                return redirect()->route('tablaporc.index')->with('success', 'Registro creado exitosamente');
            } catch (Exception $e) {
                // Capturar excepciones y redirigir con mensaje de error
                return redirect()->back()->with('error', 'Error al crear el registro: ' . $e->getMessage());
            }
        } else {
            return view('sinpermiso.index');
        }
    }



    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'editar');
        if ($tienePermiso) {
            $id = $request->input('id');
            $estado = $request->input('estado');
            $cuota = $request->input('cuota1');
            $porcentaje = $request->input('porcentaje1');
            // Buscar la entrada en la tabla TablaPorcentaje
            $porcentajeEntry = TablaPorcentaje::find($id);
            if ($porcentajeEntry) {
                // Actualizar los valores
                $porcentajeEntry->estado = $estado;
                $porcentajeEntry->cuota = $cuota;
                $porcentajeEntry->porcentaje = $porcentaje;
                
                // Guardar los cambios
                $porcentajeEntry->save();
                
                // Redireccionar con un mensaje de éxito
                return redirect()->back()->with('success', '¡La entrada se ha actualizado correctamente!');
            } else {
                // Redireccionar con un mensaje de error si no se encuentra la entrada
                return redirect()->back()->with('error', '¡No se encontró la entrada correspondiente en la tabla!');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $tabla = TablaPorcentaje::find($id);
        $tienePermiso = $this->permisoService->verificarPermiso('Producto', 'borrar');
        if ($tienePermiso) {
            if (!$tabla) {
                // Maneja el caso en que el producto no existe
                return redirect()->back()->with('error', 'Producto no encontrado.');
            }

            // Intenta eliminar el producto y maneja restricciones de clave foránea
            try {
                $tabla->estado = 0;
                $tabla->save();
                return redirect()->route('tablaporc.index')->with('success', 'Registro desactivado con éxito.');
            } catch (\Illuminate\Database\QueryException $e) {
                // Maneja una excepción que indica restricciones de clave foránea
                return redirect()->back()->with('error', 'No se puede desactivar el registro debido a restricciones de clave foránea.');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }
}
