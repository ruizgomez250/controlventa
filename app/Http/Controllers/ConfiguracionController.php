<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configuraciones = Configuracion::all();
        return view('configuracion.index', compact('configuraciones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('configuraciones.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {

                // Condicionv
                $condicionv = Configuracion::firstOrCreate(
                    ['descripcion' => 'condicionv'],
                    ['estado' => 1]
                );

                $condicionv->estado = ($request->input('condicion') == 'cadavez') ? 1 : 0;
                $condicionv->save();

                // Ventas / pagos
                $ventas = Configuracion::firstOrCreate(
                    ['descripcion' => 'ventas'],
                    ['estado' => 0]
                );

                $ventas->estado = $request->has('pagos') ? 1 : 0;
                $ventas->save();

                // Idioma
                if ($request->has('idioma')) {
                    $idioma = Configuracion::firstOrCreate(
                        ['descripcion' => 'idioma'],
                        ['observacion' => 'es']
                    );
                    $idioma->observacion = $request->input('idioma');
                    $idioma->save();
                    session(['app_locale' => $request->input('idioma')]);
                }

                // Moneda
                if ($request->has('moneda')) {
                    $moneda = Configuracion::firstOrCreate(
                        ['descripcion' => 'moneda'],
                        ['observacion' => 'Gs.']
                    );
                    $moneda->observacion = $request->input('moneda');
                    $moneda->save();
                }
            });

            return redirect()->route('configuracion.index')
                ->with('success', 'Configuración actualizada con éxito!!');
        } catch (\Exception $e) {
            // Opcional: puedes loguear el error
            

            return redirect()->route('configuracion.index')
                ->with('error', 'Ocurrió un error al actualizar la configuración.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function show(Configuracion $configuracion)
    {
        return view('configuraciones.show', compact('configuracion'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function edit(Configuracion $configuracion)
    {
        return view('configuraciones.edit', compact('configuracion'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Configuracion $configuracion)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'estado' => 'required|boolean',
            'observacion' => 'nullable|string|max:255',
        ]);

        $configuracion->update($request->all());

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuracion updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Configuracion $configuracion)
    {
        $configuracion->delete();

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuracion deleted successfully');
    }
}
